<?php
declare(strict_types=1);
/**
 * AI Designer - Theme Exporter
 * 
 * STEP 5: Exports generated HTML/PHP theme to TB JSON format.
 * Uses the existing HTML Converter to transform pages, header, footer.
 * 
 * Creates dual output:
 * - Original HTML/PHP files (preserved)
 * - TB JSON export (for Theme Builder editing)
 *
 * @package AiDesigner
 * @version 4.0
 */

namespace Core\AiDesigner;

class ThemeExporter
{
    private $converter;
    
    public function __construct()
    {
        // Load the HTML to TB Converter
        require_once dirname(__DIR__) . '/theme-builder/html-converter/Converter.php';
        $this->converter = new \Core\ThemeBuilder\HtmlConverter\Converter();
    }

    /**
     * Export theme to TB JSON format
     */
    public function exportToTB(Theme $theme): void
    {
        $exportPath = $theme->getTbExportPath();
        
        // Ensure export directory exists
        if (!is_dir($exportPath)) {
            mkdir($exportPath, 0755, true);
        }
        if (!is_dir($exportPath . '/pages')) {
            mkdir($exportPath . '/pages', 0755, true);
        }
        
        $tbExport = [
            'theme' => [
                'name' => $theme->getName(),
                'slug' => $theme->getSlug(),
                'version' => '1.0.0',
                'exported_at' => date('Y-m-d H:i:s'),
                'design_system' => $theme->getDesignSystem()
            ],
            'header' => null,
            'footer' => null,
            'pages' => []
        ];

        // Convert header
        $headerHtml = $theme->getHeader();
        if ($headerHtml) {
            $headerJson = $this->convertHeader($headerHtml);
            $tbExport['header'] = $headerJson;
            $theme->setHeaderJson($headerJson);
            $this->saveJson($exportPath . '/header.json', $headerJson);
        }

        // Convert footer
        $footerHtml = $theme->getFooter();
        if ($footerHtml) {
            $footerJson = $this->convertFooter($footerHtml);
            $tbExport['footer'] = $footerJson;
            $theme->setFooterJson($footerJson);
            $this->saveJson($exportPath . '/footer.json', $footerJson);
        }

        // Convert pages
        foreach ($theme->getPages() as $pageName => $pageHtml) {
            $pageJson = $this->convertPage($pageName, $pageHtml, $theme->getDesignSystem());
            $tbExport['pages'][$pageName] = $pageJson;
            $theme->addPageTbExport($pageName, $pageJson);
            
            $filename = strtolower(str_replace([' ', '-'], '_', $pageName)) . '.json';
            $this->saveJson($exportPath . '/pages/' . $filename, $pageJson);
        }

        // Save complete export
        $this->saveJson($exportPath . '/theme-export.json', $tbExport);
        
        // Update theme object
        $theme->setTbExport($tbExport);
    }

    /**
     * Convert header HTML to TB JSON
     */
    private function convertHeader(string $html): array
    {
        // Extract just the header element
        $headerHtml = $this->extractElement($html, 'header');
        
        if (empty($headerHtml)) {
            // Return default header structure
            return $this->getDefaultHeaderJson();
        }
        
        try {
            $converted = $this->converter->convert($headerHtml);
            
            return [
                'type' => 'header',
                'name' => 'Site Header',
                'sections' => $converted['sections'] ?? [],
                'settings' => [
                    'sticky' => true,
                    'transparent' => false,
                    'height' => 80
                ]
            ];
        } catch (\Exception $e) {
            error_log("[AI-Designer] Header conversion failed: " . $e->getMessage());
            return $this->getDefaultHeaderJson();
        }
    }

    /**
     * Convert footer HTML to TB JSON
     */
    private function convertFooter(string $html): array
    {
        // Extract just the footer element
        $footerHtml = $this->extractElement($html, 'footer');
        
        if (empty($footerHtml)) {
            return $this->getDefaultFooterJson();
        }
        
        try {
            $converted = $this->converter->convert($footerHtml);
            
            return [
                'type' => 'footer',
                'name' => 'Site Footer',
                'sections' => $converted['sections'] ?? [],
                'settings' => [
                    'columns' => 4,
                    'showNewsletter' => true,
                    'showSocial' => true
                ]
            ];
        } catch (\Exception $e) {
            error_log("[AI-Designer] Footer conversion failed: " . $e->getMessage());
            return $this->getDefaultFooterJson();
        }
    }

    /**
     * Convert page HTML to TB JSON
     */
    private function convertPage(string $pageName, string $html, array $designSystem): array
    {
        // Extract main content
        $mainHtml = $this->extractElement($html, 'main');
        
        if (empty($mainHtml)) {
            // Try to extract body content
            $mainHtml = $this->extractBodyContent($html);
        }
        
        if (empty($mainHtml)) {
            return $this->getDefaultPageJson($pageName);
        }
        
        try {
            $converted = $this->converter->convert($mainHtml);
            
            return [
                'type' => 'page',
                'name' => ucfirst(str_replace(['-', '_'], ' ', $pageName)),
                'slug' => strtolower(str_replace([' ', '-'], '_', $pageName)),
                'sections' => $converted['sections'] ?? [],
                'meta' => [
                    'title' => ucfirst($pageName),
                    'description' => '',
                    'keywords' => ''
                ],
                'design_system' => $designSystem['style'] ?? 'modern'
            ];
        } catch (\Exception $e) {
            error_log("[AI-Designer] Page conversion failed for {$pageName}: " . $e->getMessage());
            return $this->getDefaultPageJson($pageName);
        }
    }

    /**
     * Extract specific element from HTML
     */
    private function extractElement(string $html, string $tag): string
    {
        $doc = new \DOMDocument();
        $doc->encoding = 'UTF-8';
        
        libxml_use_internal_errors(true);
        @$doc->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();
        
        $elements = $doc->getElementsByTagName($tag);
        
        if ($elements->length > 0) {
            $element = $elements->item(0);
            return $doc->saveHTML($element);
        }
        
        return '';
    }

    /**
     * Extract body content from full HTML
     */
    private function extractBodyContent(string $html): string
    {
        // Remove PHP tags first
        $html = preg_replace('/<\?php.*?\?>/s', '', $html);
        
        $doc = new \DOMDocument();
        $doc->encoding = 'UTF-8';
        
        libxml_use_internal_errors(true);
        @$doc->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();
        
        $body = $doc->getElementsByTagName('body');
        
        if ($body->length > 0) {
            $bodyContent = '';
            foreach ($body->item(0)->childNodes as $child) {
                $bodyContent .= $doc->saveHTML($child);
            }
            return $bodyContent;
        }
        
        // Return as-is if no body tag
        return $html;
    }

    /**
     * Save JSON to file
     */
    private function saveJson(string $path, array $data): void
    {
        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        file_put_contents($path, $json);
        chmod($path, 0644);
    }

    /**
     * Get default header JSON structure
     */
    private function getDefaultHeaderJson(): array
    {
        return [
            'type' => 'header',
            'name' => 'Site Header',
            'sections' => [
                [
                    'id' => 'header-section-1',
                    'name' => 'Header',
                    'design' => [
                        'background' => ['type' => 'color', 'value' => '#ffffff'],
                        'padding' => ['top' => 20, 'bottom' => 20]
                    ],
                    'rows' => [
                        [
                            'id' => 'header-row-1',
                            'columns' => [
                                [
                                    'id' => 'header-col-1',
                                    'width' => 3,
                                    'modules' => [
                                        [
                                            'type' => 'text',
                                            'id' => 'logo-text',
                                            'content' => [
                                                'text' => '<h1>Logo</h1>'
                                            ]
                                        ]
                                    ]
                                ],
                                [
                                    'id' => 'header-col-2',
                                    'width' => 9,
                                    'modules' => [
                                        [
                                            'type' => 'menu',
                                            'id' => 'main-menu',
                                            'content' => [
                                                'items' => []
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            'settings' => [
                'sticky' => true,
                'transparent' => false,
                'height' => 80
            ]
        ];
    }

    /**
     * Get default footer JSON structure
     */
    private function getDefaultFooterJson(): array
    {
        return [
            'type' => 'footer',
            'name' => 'Site Footer',
            'sections' => [
                [
                    'id' => 'footer-section-1',
                    'name' => 'Footer',
                    'design' => [
                        'background' => ['type' => 'color', 'value' => '#1a1a1a'],
                        'padding' => ['top' => 60, 'bottom' => 40]
                    ],
                    'rows' => [
                        [
                            'id' => 'footer-row-1',
                            'columns' => [
                                [
                                    'id' => 'footer-col-1',
                                    'width' => 4,
                                    'modules' => [
                                        [
                                            'type' => 'text',
                                            'id' => 'footer-about',
                                            'content' => [
                                                'text' => '<h4>About Us</h4><p>Company description</p>'
                                            ]
                                        ]
                                    ]
                                ],
                                [
                                    'id' => 'footer-col-2',
                                    'width' => 2,
                                    'modules' => [
                                        [
                                            'type' => 'menu',
                                            'id' => 'footer-menu',
                                            'content' => [
                                                'title' => 'Quick Links',
                                                'items' => []
                                            ]
                                        ]
                                    ]
                                ],
                                [
                                    'id' => 'footer-col-3',
                                    'width' => 3,
                                    'modules' => [
                                        [
                                            'type' => 'contact_info',
                                            'id' => 'footer-contact',
                                            'content' => [
                                                'title' => 'Contact',
                                                'address' => '123 Street',
                                                'phone' => '+1 234 567 890',
                                                'email' => 'info@example.com'
                                            ]
                                        ]
                                    ]
                                ],
                                [
                                    'id' => 'footer-col-4',
                                    'width' => 3,
                                    'modules' => [
                                        [
                                            'type' => 'newsletter',
                                            'id' => 'footer-newsletter',
                                            'content' => [
                                                'title' => 'Newsletter',
                                                'placeholder' => 'Your email',
                                                'button_text' => 'Subscribe'
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            'settings' => [
                'columns' => 4,
                'showNewsletter' => true,
                'showSocial' => true
            ]
        ];
    }

    /**
     * Get default page JSON structure
     */
    private function getDefaultPageJson(string $pageName): array
    {
        $title = ucfirst(str_replace(['-', '_'], ' ', $pageName));
        
        return [
            'type' => 'page',
            'name' => $title,
            'slug' => strtolower(str_replace([' ', '-'], '_', $pageName)),
            'sections' => [
                [
                    'id' => 'page-section-1',
                    'name' => 'Hero',
                    'design' => [
                        'background' => ['type' => 'color', 'value' => 'var(--color-primary)'],
                        'padding' => ['top' => 100, 'bottom' => 100]
                    ],
                    'rows' => [
                        [
                            'id' => 'hero-row-1',
                            'columns' => [
                                [
                                    'id' => 'hero-col-1',
                                    'width' => 12,
                                    'modules' => [
                                        [
                                            'type' => 'heading',
                                            'id' => 'page-title',
                                            'content' => [
                                                'text' => $title,
                                                'tag' => 'h1',
                                                'alignment' => 'center'
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            'meta' => [
                'title' => $title,
                'description' => '',
                'keywords' => ''
            ]
        ];
    }

    /**
     * Export single page to TB JSON (standalone use)
     */
    public function exportPageToTB(string $html, string $pageName): array
    {
        return $this->convertPage($pageName, $html, []);
    }

    /**
     * Export header to TB JSON (standalone use)
     */
    public function exportHeaderToTB(string $html): array
    {
        return $this->convertHeader($html);
    }

    /**
     * Export footer to TB JSON (standalone use)
     */
    public function exportFooterToTB(string $html): array
    {
        return $this->convertFooter($html);
    }
}
