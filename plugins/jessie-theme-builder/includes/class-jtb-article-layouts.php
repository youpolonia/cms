<?php
/**
 * Article Layouts
 * Predefined layouts for importing articles into JTB
 *
 * @package JessieThemeBuilder
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

class JTB_Article_Layouts
{
    private static int $idCounter = 0;

    /**
     * Get available layouts
     */
    public static function getLayouts(): array
    {
        return [
            'classic' => [
                'name' => 'Classic',
                'description' => 'Featured image on top, title below, then content',
                'icon' => 'layout',
                'preview' => 'classic'
            ],
            'hero' => [
                'name' => 'Hero Banner',
                'description' => 'Full-width hero with image background and title overlay',
                'icon' => 'maximize',
                'preview' => 'hero'
            ],
            'magazine' => [
                'name' => 'Magazine',
                'description' => 'Image on left (40%), content on right (60%)',
                'icon' => 'columns',
                'preview' => 'magazine'
            ],
            'minimal' => [
                'name' => 'Minimal',
                'description' => 'Clean text-only layout, no featured image',
                'icon' => 'file-text',
                'preview' => 'minimal'
            ]
        ];
    }

    /**
     * Apply layout to parsed content
     *
     * @param string $layout Layout key
     * @param array $modules Parsed content modules (headings, text, images, etc.)
     * @param string|null $featuredImage Featured image URL
     * @param string|null $title Article title
     * @return array JTB content structure
     */
    public static function applyLayout(string $layout, array $modules, ?string $featuredImage = null, ?string $title = null): array
    {
        self::$idCounter = 0;

        switch ($layout) {
            case 'hero':
                return self::layoutHero($modules, $featuredImage, $title);
            case 'magazine':
                return self::layoutMagazine($modules, $featuredImage, $title);
            case 'minimal':
                return self::layoutMinimal($modules, $title);
            case 'classic':
            default:
                return self::layoutClassic($modules, $featuredImage, $title);
        }
    }

    /**
     * Classic Layout
     * - Section 1: Featured image (full width)
     * - Section 2: Title + Content
     */
    private static function layoutClassic(array $modules, ?string $featuredImage, ?string $title): array
    {
        $sections = [];

        // Section 1: Featured Image (if exists)
        if ($featuredImage) {
            $sections[] = [
                'type' => 'section',
                'id' => self::generateId('section'),
                'attrs' => [
                    'fullwidth' => true,
                    'inner_width' => 1200,
                    'padding' => ['top' => 0, 'right' => 0, 'bottom' => 0, 'left' => 0]
                ],
                'children' => [
                    [
                        'type' => 'row',
                        'id' => self::generateId('row'),
                        'attrs' => ['columns' => '1'],
                        'children' => [
                            [
                                'type' => 'column',
                                'id' => self::generateId('column'),
                                'attrs' => [],
                                'children' => [
                                    [
                                        'type' => 'image',
                                        'id' => self::generateId('image'),
                                        'attrs' => [
                                            'src' => $featuredImage,
                                            'alt' => 'Featured Image',
                                            'align' => 'center',
                                            'max_width' => '100%'
                                        ],
                                        'children' => []
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ];
        }

        // Section 2: Title + Content
        $contentChildren = [];

        // Add title if provided and not already in modules
        if ($title) {
            $contentChildren[] = [
                'type' => 'heading',
                'id' => self::generateId('heading'),
                'attrs' => [
                    'text' => $title,
                    'level' => 'h1'
                ],
                'children' => []
            ];
        }

        // Add all content modules
        foreach ($modules as $module) {
            $contentChildren[] = $module;
        }

        $sections[] = [
            'type' => 'section',
            'id' => self::generateId('section'),
            'attrs' => [
                'fullwidth' => false,
                'inner_width' => 800,
                'padding' => ['top' => 60, 'right' => 20, 'bottom' => 60, 'left' => 20]
            ],
            'children' => [
                [
                    'type' => 'row',
                    'id' => self::generateId('row'),
                    'attrs' => ['columns' => '1'],
                    'children' => [
                        [
                            'type' => 'column',
                            'id' => self::generateId('column'),
                            'attrs' => [],
                            'children' => $contentChildren
                        ]
                    ]
                ]
            ]
        ];

        return [
            'version' => '1.0',
            'content' => $sections
        ];
    }

    /**
     * Hero Layout
     * - Section 1: Fullwidth hero with background image + title overlay
     * - Section 2: Content
     */
    private static function layoutHero(array $modules, ?string $featuredImage, ?string $title): array
    {
        $sections = [];

        // Section 1: Hero with background image
        $heroChildren = [];

        if ($title) {
            $heroChildren[] = [
                'type' => 'heading',
                'id' => self::generateId('heading'),
                'attrs' => [
                    'text' => $title,
                    'level' => 'h1',
                    'text_color' => '#ffffff',
                    'text_align' => 'center'
                ],
                'children' => []
            ];
        }

        $heroAttrs = [
            'fullwidth' => true,
            'inner_width' => 1200,
            'min_height' => 400,
            'vertical_align' => 'center',
            'padding' => ['top' => 100, 'right' => 30, 'bottom' => 100, 'left' => 30]
        ];

        // Add background image if exists
        if ($featuredImage) {
            $heroAttrs['background_type'] = 'image';
            $heroAttrs['background_image'] = $featuredImage;
            $heroAttrs['background_size'] = 'cover';
            $heroAttrs['background_position'] = 'center center';
            $heroAttrs['background_image_overlay'] = 'rgba(0,0,0,0.5)';
        } else {
            $heroAttrs['background_type'] = 'color';
            $heroAttrs['background_color'] = '#1a1a2e';
        }

        $sections[] = [
            'type' => 'section',
            'id' => self::generateId('section'),
            'attrs' => $heroAttrs,
            'children' => [
                [
                    'type' => 'row',
                    'id' => self::generateId('row'),
                    'attrs' => ['columns' => '1'],
                    'children' => [
                        [
                            'type' => 'column',
                            'id' => self::generateId('column'),
                            'attrs' => ['text_align' => 'center'],
                            'children' => $heroChildren
                        ]
                    ]
                ]
            ]
        ];

        // Section 2: Content
        if (!empty($modules)) {
            $sections[] = [
                'type' => 'section',
                'id' => self::generateId('section'),
                'attrs' => [
                    'fullwidth' => false,
                    'inner_width' => 800,
                    'padding' => ['top' => 60, 'right' => 20, 'bottom' => 60, 'left' => 20]
                ],
                'children' => [
                    [
                        'type' => 'row',
                        'id' => self::generateId('row'),
                        'attrs' => ['columns' => '1'],
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
            ];
        }

        return [
            'version' => '1.0',
            'content' => $sections
        ];
    }

    /**
     * Magazine Layout
     * - Section 1: Two columns - image left (40%), title+intro right (60%)
     * - Section 2: Full width content
     */
    private static function layoutMagazine(array $modules, ?string $featuredImage, ?string $title): array
    {
        $sections = [];

        // Extract first paragraph as intro
        $introModule = null;
        $remainingModules = [];
        $foundIntro = false;

        foreach ($modules as $module) {
            if (!$foundIntro && $module['type'] === 'text') {
                $introModule = $module;
                $foundIntro = true;
            } else {
                $remainingModules[] = $module;
            }
        }

        // Section 1: Magazine header (image left, title+intro right)
        $leftColumnChildren = [];
        $rightColumnChildren = [];

        // Left column: Image
        if ($featuredImage) {
            $leftColumnChildren[] = [
                'type' => 'image',
                'id' => self::generateId('image'),
                'attrs' => [
                    'src' => $featuredImage,
                    'alt' => 'Featured Image',
                    'align' => 'center',
                    'border_radius' => '8px'
                ],
                'children' => []
            ];
        }

        // Right column: Title + Intro
        if ($title) {
            $rightColumnChildren[] = [
                'type' => 'heading',
                'id' => self::generateId('heading'),
                'attrs' => [
                    'text' => $title,
                    'level' => 'h1'
                ],
                'children' => []
            ];
        }

        if ($introModule) {
            $rightColumnChildren[] = $introModule;
        }

        $sections[] = [
            'type' => 'section',
            'id' => self::generateId('section'),
            'attrs' => [
                'fullwidth' => false,
                'inner_width' => 1200,
                'padding' => ['top' => 60, 'right' => 20, 'bottom' => 40, 'left' => 20]
            ],
            'children' => [
                [
                    'type' => 'row',
                    'id' => self::generateId('row'),
                    'attrs' => [
                        'columns' => '1_3,2_3',
                        'column_gap' => 30,
                        'equal_heights' => false
                    ],
                    'children' => [
                        [
                            'type' => 'column',
                            'id' => self::generateId('column'),
                            'attrs' => [],
                            'children' => $leftColumnChildren
                        ],
                        [
                            'type' => 'column',
                            'id' => self::generateId('column'),
                            'attrs' => ['vertical_align' => 'center'],
                            'children' => $rightColumnChildren
                        ]
                    ]
                ]
            ]
        ];

        // Section 2: Remaining content
        if (!empty($remainingModules)) {
            $sections[] = [
                'type' => 'section',
                'id' => self::generateId('section'),
                'attrs' => [
                    'fullwidth' => false,
                    'inner_width' => 800,
                    'padding' => ['top' => 40, 'right' => 20, 'bottom' => 60, 'left' => 20]
                ],
                'children' => [
                    [
                        'type' => 'row',
                        'id' => self::generateId('row'),
                        'attrs' => ['columns' => '1'],
                        'children' => [
                            [
                                'type' => 'column',
                                'id' => self::generateId('column'),
                                'attrs' => [],
                                'children' => $remainingModules
                            ]
                        ]
                    ]
                ]
            ];
        }

        return [
            'version' => '1.0',
            'content' => $sections
        ];
    }

    /**
     * Minimal Layout
     * - No featured image
     * - Clean typography focused layout
     */
    private static function layoutMinimal(array $modules, ?string $title): array
    {
        $contentChildren = [];

        // Add title with elegant styling
        if ($title) {
            $contentChildren[] = [
                'type' => 'heading',
                'id' => self::generateId('heading'),
                'attrs' => [
                    'text' => $title,
                    'level' => 'h1',
                    'text_align' => 'center',
                    'margin_bottom' => '40px'
                ],
                'children' => []
            ];

            // Add divider after title
            $contentChildren[] = [
                'type' => 'divider',
                'id' => self::generateId('divider'),
                'attrs' => [
                    'style' => 'solid',
                    'color' => '#e0e0e0',
                    'width' => '100px',
                    'thickness' => '2px',
                    'align' => 'center',
                    'margin_top' => '0px',
                    'margin_bottom' => '40px'
                ],
                'children' => []
            ];
        }

        // Add all content modules
        foreach ($modules as $module) {
            $contentChildren[] = $module;
        }

        return [
            'version' => '1.0',
            'content' => [
                [
                    'type' => 'section',
                    'id' => self::generateId('section'),
                    'attrs' => [
                        'fullwidth' => false,
                        'inner_width' => 720,
                        'padding' => ['top' => 80, 'right' => 20, 'bottom' => 80, 'left' => 20],
                        'background_type' => 'color',
                        'background_color' => '#ffffff'
                    ],
                    'children' => [
                        [
                            'type' => 'row',
                            'id' => self::generateId('row'),
                            'attrs' => ['columns' => '1'],
                            'children' => [
                                [
                                    'type' => 'column',
                                    'id' => self::generateId('column'),
                                    'attrs' => [],
                                    'children' => $contentChildren
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }

    /**
     * Generate unique ID
     */
    private static function generateId(string $prefix): string
    {
        self::$idCounter++;
        return $prefix . '_layout_' . self::$idCounter . '_' . substr(bin2hex(random_bytes(4)), 0, 8);
    }
}
