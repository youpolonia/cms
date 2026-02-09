<?php
/**
 * JTB AI Agent Images
 *
 * Fetches and assigns images from Pexels API to all modules that need images.
 * Works with PATH-based references from path_map.
 *
 * Uses existing JTB_AI_Pexels class for image fetching.
 *
 * IMAGE ASSIGNMENT STRATEGY:
 * 1. Hero images - large, landscape, industry-specific
 * 2. Team photos - portrait orientation, professional headshots
 * 3. Feature images - medium size, context-specific
 * 4. Gallery images - multiple images per gallery module
 * 5. Background images - abstract or industry textures
 * 6. Testimonial portraits - small headshots
 * 7. Logo placeholders - generic business imagery
 *
 * ZERO HARDCODES - All image contexts derived from module paths and session data.
 *
 * @package JessieThemeBuilder
 * @since 2.0.0
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

class JTB_AI_Agent_Images
{
    /**
     * Modules that require images
     */
    private const IMAGE_MODULES = [
        // Direct image modules
        'image' => 'single',
        'fullwidth_image' => 'single',

        // Modules with image fields
        'blurb' => 'icon_or_image',
        'testimonial' => 'portrait',
        'team_member' => 'portrait',
        'pricing_table' => 'icon',
        'cta' => 'background',
        'fullwidth_header' => 'background',

        // Gallery/slider modules
        'gallery' => 'multiple',
        'slider' => 'multiple',
        'fullwidth_slider' => 'multiple',
        'post_slider' => 'multiple',

        // Theme modules
        'site_logo' => 'logo',
        'featured_image' => 'single'
    ];

    /**
     * Section blueprints that typically need background images
     */
    private const BACKGROUND_SECTIONS = [
        'hero',
        'fullwidth_hero',
        'cta',
        'call_to_action',
        'final_cta',
        'parallax',
        'stats',
        'counters'
    ];

    /**
     * Execute image fetching and assignment
     *
     * @param array $session Multi-agent session data
     * @return array ['ok' => bool, 'images' => [...], 'stats' => [...]]
     */
    public static function execute(array $session): array
    {
        $startTime = microtime(true);

        // Check if Pexels is configured
        if (!class_exists(__NAMESPACE__ . '\\JTB_AI_Pexels') || !JTB_AI_Pexels::isConfigured()) {
            return [
                'ok' => true,
                'images' => [],
                'stats' => [
                    'time_ms' => 0,
                    'images_fetched' => 0,
                    'warning' => 'Pexels API not configured. Images not fetched.'
                ]
            ];
        }

        // Clear Pexels cache for fresh images
        JTB_AI_Pexels::clearUsedPhotosCache();

        // Extract context for image fetching
        $context = self::buildImageContext($session);

        // Get path map for module assignments
        $pathMap = $session['path_map'] ?? [];
        $skeleton = $session['skeleton'] ?? [];

        // Collect all image assignments
        $images = [];
        $stats = [
            'heroes' => 0,
            'portraits' => 0,
            'features' => 0,
            'galleries' => 0,
            'backgrounds' => 0,
            'logos' => 0,
            'total' => 0,
            'errors' => 0
        ];

        // Process header images
        $headerImages = self::processRegion('header', $skeleton['header'] ?? [], $pathMap, $context);
        $images = array_merge($images, $headerImages['images']);
        $stats = self::mergeStats($stats, $headerImages['stats']);

        // Process footer images
        $footerImages = self::processRegion('footer', $skeleton['footer'] ?? [], $pathMap, $context);
        $images = array_merge($images, $footerImages['images']);
        $stats = self::mergeStats($stats, $footerImages['stats']);

        // Process page images
        foreach ($skeleton['pages'] ?? [] as $pageName => $pageData) {
            $pageContext = array_merge($context, ['page' => $pageName]);
            $pageImages = self::processPage($pageName, $pageData, $pathMap, $pageContext);
            $images = array_merge($images, $pageImages['images']);
            $stats = self::mergeStats($stats, $pageImages['stats']);
        }

        $stats['total'] = count($images);
        $stats['time_ms'] = (int)((microtime(true) - $startTime) * 1000);

        return [
            'ok' => true,
            'images' => $images,
            'tokens_used' => 0, // Images agent doesn't use AI tokens
            'stats' => $stats
        ];
    }

    /**
     * Build image context from session
     */
    private static function buildImageContext(array $session): array
    {
        return [
            'industry' => $session['industry'] ?? 'general',
            'style' => $session['style'] ?? 'modern',
            'prompt' => $session['prompt'] ?? '',
            'business_keywords' => self::extractBusinessKeywords($session['prompt'] ?? ''),
            'color_scheme' => $session['color_scheme'] ?? []
        ];
    }

    /**
     * Extract business keywords from prompt for image search
     */
    private static function extractBusinessKeywords(string $prompt): string
    {
        // Remove common stop words and extract key terms
        $stopWords = ['a', 'an', 'the', 'for', 'and', 'or', 'but', 'website', 'site', 'page', 'with', 'that', 'this', 'to', 'of', 'in', 'on'];
        $words = preg_split('/\s+/', strtolower($prompt));
        $keywords = array_filter($words, function($word) use ($stopWords) {
            return strlen($word) > 2 && !in_array($word, $stopWords);
        });

        return implode(' ', array_slice($keywords, 0, 5));
    }

    /**
     * Process a region (header/footer) for images
     */
    private static function processRegion(string $region, array $regionData, array $pathMap, array $context): array
    {
        $images = [];
        $stats = ['heroes' => 0, 'portraits' => 0, 'features' => 0, 'galleries' => 0, 'backgrounds' => 0, 'logos' => 0, 'errors' => 0];

        // Recursively find modules
        $modules = self::findModulesRecursive($regionData);

        foreach ($modules as $module) {
            $moduleType = $module['type'] ?? '';
            $modulePath = $module['_path'] ?? $module['id'] ?? '';
            $moduleId = $module['id'] ?? '';

            if (empty($modulePath) && !empty($moduleId)) {
                // Find path from map
                $modulePath = array_search($moduleId, $pathMap);
            }

            if (empty($modulePath)) {
                continue;
            }

            // Check if this module needs images
            $imageType = self::IMAGE_MODULES[$moduleType] ?? null;
            if (!$imageType) {
                continue;
            }

            $result = self::fetchImageForModule($moduleType, $imageType, $modulePath, $context);

            if (!empty($result)) {
                $images[$modulePath] = $result['attrs'];
                $stats[$result['stat_key']]++;
            } else {
                $stats['errors']++;
            }
        }

        return ['images' => $images, 'stats' => $stats];
    }

    /**
     * Process a page for images
     */
    private static function processPage(string $pageName, array $pageData, array $pathMap, array $context): array
    {
        $images = [];
        $stats = ['heroes' => 0, 'portraits' => 0, 'features' => 0, 'galleries' => 0, 'backgrounds' => 0, 'logos' => 0, 'errors' => 0];

        foreach ($pageData['sections'] ?? [] as $section) {
            $blueprint = $section['attrs']['_pattern'] ?? 'generic';
            // Build section path from id using pathMap
            $sectionId = $section['id'] ?? '';
            $sectionPath = !empty($sectionId) ? array_search($sectionId, $pathMap) : '';
            if ($sectionPath === false) $sectionPath = '';

            // Check if section needs background image
            if (in_array($blueprint, self::BACKGROUND_SECTIONS)) {
                $bgResult = self::fetchBackgroundImage($blueprint, $sectionPath, $context);
                if (!empty($bgResult)) {
                    $images[$sectionPath] = $bgResult['attrs'];
                    $stats['backgrounds']++;
                }
            }

            // Process modules in section
            $modules = self::findModulesRecursive($section);

            foreach ($modules as $module) {
                $moduleType = $module['type'] ?? '';
                $moduleId = $module['id'] ?? '';
                $modulePath = !empty($moduleId) ? array_search($moduleId, $pathMap) : '';
                if ($modulePath === false) $modulePath = '';
                $moduleBlueprint = $blueprint; // Use section blueprint for modules

                if (empty($modulePath)) {
                    continue;
                }

                // Check if this module needs images
                $imageType = self::IMAGE_MODULES[$moduleType] ?? null;
                if (!$imageType) {
                    continue;
                }

                // Add blueprint context
                $moduleContext = array_merge($context, [
                    'blueprint' => $moduleBlueprint,
                    'page' => $pageName
                ]);

                $result = self::fetchImageForModule($moduleType, $imageType, $modulePath, $moduleContext);

                if (!empty($result)) {
                    $images[$modulePath] = $result['attrs'];
                    $stats[$result['stat_key']]++;
                } else {
                    $stats['errors']++;
                }
            }
        }

        return ['images' => $images, 'stats' => $stats];
    }

    /**
     * Recursively find all modules in a structure
     */
    private static function findModulesRecursive(array $element): array
    {
        $modules = [];

        // If this element has a type that's not structural, it's a module
        $type = $element['type'] ?? '';
        if (!empty($type) && !in_array($type, ['section', 'row', 'column'])) {
            $modules[] = $element;
        }

        // Check children
        if (!empty($element['children'])) {
            foreach ($element['children'] as $child) {
                $modules = array_merge($modules, self::findModulesRecursive($child));
            }
        }

        // Check sections
        if (!empty($element['sections'])) {
            foreach ($element['sections'] as $section) {
                $modules = array_merge($modules, self::findModulesRecursive($section));
            }
        }

        return $modules;
    }

    /**
     * Fetch image for a specific module
     */
    private static function fetchImageForModule(string $moduleType, string $imageType, string $path, array $context): ?array
    {
        $blueprint = $context['blueprint'] ?? self::extractBlueprint($path);
        $industry = $context['industry'] ?? 'general';

        switch ($imageType) {
            case 'single':
                return self::fetchSingleImage($moduleType, $blueprint, $context);

            case 'portrait':
                return self::fetchPortraitImage($moduleType, $path, $context);

            case 'icon_or_image':
                // Blurbs typically use icons, but can have images
                return self::fetchBlurbImage($blueprint, $context);

            case 'background':
                return self::fetchBackgroundImage($blueprint, $path, $context);

            case 'multiple':
                return self::fetchGalleryImages($moduleType, $blueprint, $context);

            case 'logo':
                return self::fetchLogoPlaceholder($context);

            case 'icon':
                // Icons are not fetched from Pexels
                return null;

            default:
                return null;
        }
    }

    /**
     * Fetch single image (for image modules)
     */
    private static function fetchSingleImage(string $moduleType, string $blueprint, array $context): ?array
    {
        $industry = $context['industry'];

        // Determine image type based on blueprint
        if (in_array($blueprint, ['hero', 'fullwidth_hero', 'header'])) {
            $result = JTB_AI_Pexels::getHeroImage($context);
            $statKey = 'heroes';
        } elseif (in_array($blueprint, ['about', 'story', 'team_intro'])) {
            $result = JTB_AI_Pexels::getAboutImage($context);
            $statKey = 'features';
        } elseif (in_array($blueprint, ['features', 'services', 'benefits'])) {
            $featureContext = array_merge($context, ['feature' => $blueprint]);
            $result = JTB_AI_Pexels::getFeatureImage($featureContext);
            $statKey = 'features';
        } else {
            // Generic feature image
            $result = JTB_AI_Pexels::getFeatureImage($context);
            $statKey = 'features';
        }

        if (empty($result) || empty($result['url'])) {
            return null;
        }

        return [
            'attrs' => [
                'src' => $result['url'],
                'alt' => $result['alt'] ?? 'Image',
                'width' => $result['width'] ?? null,
                'height' => $result['height'] ?? null
            ],
            'stat_key' => $statKey
        ];
    }

    /**
     * Fetch portrait image (for testimonials, team members)
     */
    private static function fetchPortraitImage(string $moduleType, string $path, array $context): ?array
    {
        // Determine gender from path or content hints
        $gender = self::inferGender($path, $context);

        $personContext = array_merge($context, [
            'gender' => $gender
        ]);

        $result = JTB_AI_Pexels::getPersonPhoto($personContext);

        if (empty($result) || empty($result['url'])) {
            return null;
        }

        // Different attribute names for different modules
        $attrName = $moduleType === 'testimonial' ? 'portrait_url' : 'image';

        return [
            'attrs' => [
                $attrName => $result['url'],
                'image_alt' => $result['alt'] ?? 'Team member'
            ],
            'stat_key' => 'portraits'
        ];
    }

    /**
     * Infer gender from path or rotate
     */
    private static function inferGender(string $path, array $context): string
    {
        static $genderRotation = 0;

        // Check if content hints specify gender
        $contentHints = $context['content_hints'] ?? [];
        if (!empty($contentHints['gender'])) {
            return $contentHints['gender'];
        }

        // Extract index from path to alternate
        if (preg_match('/_(\d+)$/', $path, $matches)) {
            $index = (int)$matches[1];
            return ($index % 2 === 0) ? 'female' : 'male';
        }

        // Rotate between genders
        $genderRotation++;
        return ($genderRotation % 2 === 0) ? 'female' : 'male';
    }

    /**
     * Fetch blurb image (usually icons, but can be images)
     */
    private static function fetchBlurbImage(string $blueprint, array $context): ?array
    {
        // Most blurbs should use icons, not images
        // Only fetch image if specifically needed

        if (in_array($blueprint, ['features_with_images', 'services_detailed', 'portfolio_item'])) {
            $result = JTB_AI_Pexels::getFeatureImage(array_merge($context, ['feature' => $blueprint]));

            if (!empty($result) && !empty($result['url'])) {
                return [
                    'attrs' => [
                        'image' => $result['url'],
                        'use_icon' => false
                    ],
                    'stat_key' => 'features'
                ];
            }
        }

        return null;
    }

    /**
     * Fetch background image for section
     */
    private static function fetchBackgroundImage(string $blueprint, string $path, array $context): ?array
    {
        $backgroundType = 'abstract'; // default

        // Determine background type based on blueprint
        if (in_array($blueprint, ['hero', 'fullwidth_hero'])) {
            // Heroes often look better with industry-specific images
            $result = JTB_AI_Pexels::getHeroImage($context);
        } elseif (in_array($blueprint, ['cta', 'call_to_action', 'final_cta'])) {
            // CTAs work well with abstract or gradient backgrounds
            $backgroundType = 'gradient';
            $result = JTB_AI_Pexels::getBackgroundImage(['background_type' => $backgroundType]);
        } elseif (in_array($blueprint, ['stats', 'counters'])) {
            // Stats sections often have subtle textures
            $backgroundType = 'texture';
            $result = JTB_AI_Pexels::getBackgroundImage(['background_type' => $backgroundType]);
        } elseif (in_array($blueprint, ['parallax'])) {
            // Parallax needs large, detailed images
            $result = JTB_AI_Pexels::getBackgroundImage(['background_type' => 'nature']);
        } else {
            $result = JTB_AI_Pexels::getBackgroundImage(['background_type' => $backgroundType]);
        }

        if (empty($result) || empty($result['url'])) {
            return null;
        }

        return [
            'attrs' => [
                'background_type' => 'image', // REQUIRED for renderer to apply background_image!
                'background_image' => $result['url'],
                'background_size' => 'cover',
                'background_position' => 'center center',
                'background_image_overlay' => 'rgba(0,0,0,0.4)' // Use correct attribute name
            ],
            'stat_key' => 'backgrounds'
        ];
    }

    /**
     * Fetch multiple images for galleries/sliders
     */
    private static function fetchGalleryImages(string $moduleType, string $blueprint, array $context): ?array
    {
        $count = 6; // Default gallery count

        if ($moduleType === 'slider' || $moduleType === 'fullwidth_slider') {
            $count = 4;
        } elseif ($moduleType === 'post_slider') {
            $count = 3;
        }

        $result = JTB_AI_Pexels::getGalleryImages($context, $count);

        if (empty($result) || empty($result['images'])) {
            return null;
        }

        // Format for gallery module
        $galleryImages = [];
        foreach ($result['images'] as $idx => $image) {
            $galleryImages[] = [
                'src' => $image['url'],
                'alt' => $image['alt'] ?? "Gallery image " . ($idx + 1),
                'caption' => ''
            ];
        }

        return [
            'attrs' => [
                'images' => $galleryImages
            ],
            'stat_key' => 'galleries'
        ];
    }

    /**
     * Fetch logo placeholder
     */
    private static function fetchLogoPlaceholder(array $context): ?array
    {
        // Logos should be actual business logos, not stock photos
        // Return empty or a placeholder for now
        // In production, this would integrate with brand assets

        return [
            'attrs' => [
                'logo' => '', // Empty - should be uploaded by user
                'logo_alt' => $context['business_keywords'] ?? 'Company Logo'
            ],
            'stat_key' => 'logos'
        ];
    }

    /**
     * Extract blueprint from path
     */
    private static function extractBlueprint(string $path): string
    {
        // Path format: "page/blueprint/col/module"
        $parts = explode('/', $path);

        if (count($parts) >= 2) {
            return $parts[1];
        }

        return 'generic';
    }

    /**
     * Merge stats arrays
     */
    private static function mergeStats(array $stats1, array $stats2): array
    {
        foreach ($stats2 as $key => $value) {
            if (isset($stats1[$key]) && is_numeric($value)) {
                $stats1[$key] += $value;
            } else {
                $stats1[$key] = $value;
            }
        }
        return $stats1;
    }

    /**
     * Download and save image locally (optional feature)
     *
     * @param string $url Pexels image URL
     * @param array $metadata Image metadata
     * @return array Result with local URL
     */
    public static function downloadAndSave(string $url, array $metadata = []): array
    {
        if (!class_exists(__NAMESPACE__ . '\\JTB_AI_Pexels')) {
            return ['ok' => false, 'error' => 'Pexels class not available'];
        }

        return JTB_AI_Pexels::downloadAndSave($url, $metadata);
    }

    /**
     * Get image suggestions for a module type
     * Useful for AI prompts
     *
     * @param string $moduleType
     * @param array $context
     * @return array Search query suggestions
     */
    public static function getImageSuggestions(string $moduleType, array $context = []): array
    {
        $industry = $context['industry'] ?? 'general';
        $blueprint = $context['blueprint'] ?? 'generic';

        $suggestions = [];

        switch ($moduleType) {
            case 'image':
            case 'fullwidth_image':
                if (in_array($blueprint, ['hero', 'fullwidth_hero'])) {
                    $suggestions = [
                        "{$industry} professional office",
                        "{$industry} team working",
                        "{$industry} modern workspace",
                        "business success achievement"
                    ];
                } else {
                    $suggestions = [
                        "{$industry} professional service",
                        "business modern technology",
                        "professional team collaboration"
                    ];
                }
                break;

            case 'testimonial':
            case 'team_member':
                $suggestions = [
                    "professional portrait headshot",
                    "business person confident",
                    "corporate headshot smiling"
                ];
                break;

            case 'blurb':
                $suggestions = [
                    "{$industry} icon illustration",
                    "abstract technology concept",
                    "business service illustration"
                ];
                break;

            case 'gallery':
            case 'slider':
                $suggestions = [
                    "{$industry} portfolio work",
                    "{$industry} project showcase",
                    "professional {$industry} images"
                ];
                break;

            default:
                $suggestions = [
                    "{$industry} professional",
                    "business modern",
                    "professional service"
                ];
        }

        return $suggestions;
    }

    /**
     * Validate image URL
     *
     * @param string $url
     * @return bool
     */
    public static function validateImageUrl(string $url): bool
    {
        if (empty($url)) {
            return false;
        }

        // Check if URL is valid format
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return false;
        }

        // Check for known image hosts
        $validHosts = [
            'images.pexels.com',
            'images.unsplash.com',
            'cdn.pixabay.com',
            'localhost',
            '127.0.0.1'
        ];

        $host = parse_url($url, PHP_URL_HOST);
        if ($host && in_array($host, $validHosts)) {
            return true;
        }

        // Check for local paths
        if (strpos($url, '/media/') === 0 || strpos($url, '/uploads/') === 0) {
            return true;
        }

        // Check file extension
        $path = parse_url($url, PHP_URL_PATH);
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $validExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];

        return in_array($extension, $validExtensions);
    }

    /**
     * Get OG image for page (for SEO)
     *
     * @param string $pageName
     * @param array $pageImages
     * @return string|null Best image URL for OG
     */
    public static function getOGImage(string $pageName, array $pageImages): ?string
    {
        // Prioritize hero image
        foreach ($pageImages as $path => $attrs) {
            if (strpos($path, 'hero') !== false) {
                if (!empty($attrs['src'])) {
                    return $attrs['src'];
                }
                if (!empty($attrs['background_image'])) {
                    return $attrs['background_image'];
                }
            }
        }

        // Fallback to first image
        foreach ($pageImages as $path => $attrs) {
            if (!empty($attrs['src'])) {
                return $attrs['src'];
            }
            if (!empty($attrs['background_image'])) {
                return $attrs['background_image'];
            }
            if (!empty($attrs['image'])) {
                return $attrs['image'];
            }
        }

        return null;
    }
}
