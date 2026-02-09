<?php
/**
 * JTB AI Normalizer
 *
 * Critical middleware layer that normalizes AI-generated attributes
 * to match JTB module expectations. This fixes:
 *
 * 1. Attribute name mismatches (title → text, url → link_url)
 * 2. Type conversions (string "42" → int 42)
 * 3. Array property formats (padding_top → padding[top])
 * 4. Responsive key normalization (__mobile → __phone)
 *
 * This layer sits between AI generation and JTB rendering.
 *
 * @package JessieThemeBuilder
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

class JTB_AI_Normalizer
{
    // =========================================================
    // ATTRIBUTE NAME MAPPINGS
    // Maps AI attribute names to JTB expected names
    // =========================================================

    private static array $attributeMaps = [
        // Global mappings (apply to all modules)
        '_global' => [
            // Typography
            'fontSize' => 'font_size',
            'fontFamily' => 'font_family',
            'fontWeight' => 'font_weight',
            'lineHeight' => 'line_height',
            'letterSpacing' => 'letter_spacing',
            'textColor' => 'text_color',
            'textAlign' => 'text_align',

            // Spacing (flat to array)
            'paddingTop' => 'padding__top',
            'paddingRight' => 'padding__right',
            'paddingBottom' => 'padding__bottom',
            'paddingLeft' => 'padding__left',
            'padding_top' => 'padding__top',
            'padding_right' => 'padding__right',
            'padding_bottom' => 'padding__bottom',
            'padding_left' => 'padding__left',
            'marginTop' => 'margin__top',
            'marginRight' => 'margin__right',
            'marginBottom' => 'margin__bottom',
            'marginLeft' => 'margin__left',
            'margin_top' => 'margin__top',
            'margin_right' => 'margin__right',
            'margin_bottom' => 'margin__bottom',
            'margin_left' => 'margin__left',

            // Background
            'backgroundColor' => 'background_color',
            'backgroundImage' => 'background_image',
            'bgColor' => 'background_color',
            'bg_color' => 'background_color',

            // Border
            'borderRadius' => 'border_radius',
            'borderWidth' => 'border_width',
            'borderColor' => 'border_color',
            'borderStyle' => 'border_style',

            // Responsive (normalize all variants to JTB standard)
            '__mobile' => '__phone',
            '__sm' => '__phone',
            '__md' => '__tablet',
            '__lg' => '__tablet',

            // Common typos
            'colour' => 'color',
            'colours' => 'colors',
        ],

        // Module-specific mappings
        'heading' => [
            'title' => 'text',
            'headline' => 'text',
            'header' => 'text',
            'heading_text' => 'text',
            'tag' => 'level',
            'heading_level' => 'level',
            'size' => 'font_size',
        ],

        'text' => [
            'text' => 'content',
            'body' => 'content',
            'paragraph' => 'content',
            'description' => 'content',
        ],

        'button' => [
            'url' => 'link_url',
            'href' => 'link_url',
            'link' => 'link_url',
            'button_url' => 'link_url',
            'label' => 'text',
            'button_text' => 'text',
            'cta_text' => 'text',
        ],

        'image' => [
            'src' => 'image_url',
            'url' => 'image_url',
            'source' => 'image_url',
            'alt_text' => 'alt',
            'image_alt' => 'alt',
        ],

        'blurb' => [
            'icon' => 'font_icon',
            'icon_name' => 'font_icon',
            'icon_size' => 'icon_font_size',
            'iconSize' => 'icon_font_size',
            'heading' => 'title',
            'blurb_title' => 'title',
            'blurb_content' => 'content',
            'description' => 'content',
            'body' => 'content',
            'url' => 'link_url',
            'href' => 'link_url',
            'link' => 'link_url',
            'url_new_window' => 'link_target',
            'new_window' => 'link_target',
        ],

        'testimonial' => [
            'quote' => 'content',
            'testimonial_text' => 'content',
            'text' => 'content',
            'name' => 'author',
            'client_name' => 'author',
            'author_name' => 'author',
            'position' => 'job_title',
            'role' => 'job_title',
            'title' => 'job_title',
            'job' => 'job_title',
            'company_name' => 'company',
            'organization' => 'company',
            'image' => 'portrait_url',
            'avatar' => 'portrait_url',
            'photo' => 'portrait_url',
            'author_image' => 'portrait_url',
            'url' => 'link_url',
            'href' => 'link_url',
            'link' => 'link_url',
            'url_new_window' => 'link_target',
            'new_window' => 'link_target',
        ],

        'number_counter' => [
            'value' => 'number',
            'count' => 'number',
            'stat' => 'number',
            'label' => 'title',
            'counter_title' => 'title',
            'unit' => 'suffix',
            'counter_suffix' => 'suffix',
        ],

        'pricing_table' => [
            'plan_name' => 'title',
            'plan_title' => 'title',
            'name' => 'title',
            'cost' => 'price',
            'amount' => 'price',
            'plan_price' => 'price',
            'frequency' => 'period',
            'billing' => 'period',
            'period_text' => 'period',
            'plan_features' => 'features',
            'feature_list' => 'features',
            'items' => 'features',
            'cta' => 'button_text',
            'button_label' => 'button_text',
            'action_text' => 'button_text',
            'button_url' => 'link_url',
            'button_link' => 'link_url',
            'cta_url' => 'link_url',
            'url' => 'link_url',
            'url_new_window' => 'link_target',
            'new_window' => 'link_target',
        ],

        'accordion' => [
            'items' => 'children',
            'accordion_items' => 'children',
            'faq_items' => 'children',
        ],

        'accordion_item' => [
            'question' => 'title',
            'heading' => 'title',
            'answer' => 'content',
            'body' => 'content',
            'text' => 'content',
        ],

        'team_member' => [
            'name' => 'member_name',
            'person_name' => 'member_name',
            'full_name' => 'member_name',
            'role' => 'position',
            'job' => 'position',
            'job_title' => 'position',
            'title' => 'position',
            'bio' => 'description',
            'about' => 'description',
            'body' => 'description',
            'image' => 'image_url',
            'photo' => 'image_url',
            'avatar' => 'image_url',
            'portrait' => 'image_url',
        ],

        'video' => [
            'url' => 'video_url',
            'src' => 'video_url',
            'source' => 'video_url',
            'video_src' => 'video_url',
            'youtube' => 'video_url',
            'vimeo' => 'video_url',
        ],

        'cta' => [
            'headline' => 'title',
            'heading' => 'title',
            'cta_title' => 'title',
            'body' => 'content',
            'description' => 'content',
            'text' => 'content',
            'button_url' => 'link_url',
            'url' => 'link_url',
            'href' => 'link_url',
            'link' => 'link_url',
            'url_new_window' => 'link_target',
            'new_window' => 'link_target',
            'cta_text' => 'button_text',
            'button_label' => 'button_text',
        ],

        'contact_form' => [
            'email' => 'recipient_email',
            'to_email' => 'recipient_email',
            'send_to' => 'recipient_email',
            'submit_text' => 'submit_button_text',
            'button_label' => 'submit_button_text',
        ],

        'gallery' => [
            'images' => 'gallery_images',
            'photos' => 'gallery_images',
            'items' => 'gallery_images',
        ],

        'section' => [
            'bg_color' => 'background_color',
            'background' => 'background_color',
            'section_bg' => 'background_color',
        ],

        'row' => [
            'cols' => 'columns',
            'layout' => 'columns',
            'column_layout' => 'columns',
        ],

        'column' => [
            'width' => 'column_width',
            'col_width' => 'column_width',
        ],
    ];

    // =========================================================
    // NUMERIC PROPERTIES (need px unit in CSS)
    // =========================================================

    private static array $numericProperties = [
        'font_size', 'icon_font_size', 'title_font_size', 'content_font_size',
        'author_font_size', 'position_font_size', 'price_font_size',
        'border_width', 'min_height', 'max_width', 'width', 'height',
        'column_gap', 'row_gap', 'gap',
        'letter_spacing',
    ];

    // =========================================================
    // ARRAY PROPERTIES (need special handling)
    // =========================================================

    private static array $arrayProperties = [
        'padding' => ['top', 'right', 'bottom', 'left'],
        'margin' => ['top', 'right', 'bottom', 'left'],
        'border_radius' => ['top_left', 'top_right', 'bottom_right', 'bottom_left'],
    ];

    // =========================================================
    // AI FORMAT CONVERSION (content→children, flat→attrs)
    // =========================================================

    /**
     * Convert raw AI-generated format to JTB format.
     *
     * AI generates:   {type, padding, background_color, content: [{type: "row", columns: "1", content: [...]}]}
     * JTB expects:    {type, attrs: {padding, background_color}, children: [{type: "row", attrs: {columns: "1"}, children: [...]}]}
     *
     * This method recursively:
     * 1. Renames 'content' array (of elements) to 'children'
     * 2. Moves all flat attributes into 'attrs' object
     *
     * @param array $element Single element or entire structure
     * @return array Converted element
     */
    public static function convertAIFormat(array $element): array
    {
        if (empty($element['type'])) {
            return $element;
        }

        // Keys that belong at top level of a JTB element
        $topLevelKeys = ['type', 'id', 'children', 'attrs', '_section_type', '_role', '_pattern'];

        // Step 1: If 'content' contains nested elements, move to 'children'
        if (isset($element['content']) && is_array($element['content']) && !empty($element['content'])) {
            $firstItem = reset($element['content']);
            if (is_array($firstItem) && isset($firstItem['type'])) {
                // It's nested elements → rename to children
                if (!isset($element['children'])) {
                    $element['children'] = $element['content'];
                }
                unset($element['content']);
            }
        }

        // Step 2: Move flat attributes into 'attrs'
        if (!isset($element['attrs'])) {
            $element['attrs'] = [];
        }

        foreach (array_keys($element) as $key) {
            if (!in_array($key, $topLevelKeys)) {
                $element['attrs'][$key] = $element[$key];
                unset($element[$key]);
            }
        }

        // Step 3: Recursively convert children
        if (isset($element['children']) && is_array($element['children'])) {
            $element['children'] = array_map(function ($child) {
                return is_array($child) ? self::convertAIFormat($child) : $child;
            }, $element['children']);
        }

        return $element;
    }

    /**
     * Convert an array of sections from AI format to JTB format.
     *
     * @param array $sections Array of section elements
     * @return array Converted sections
     */
    public static function convertAISections(array $sections): array
    {
        return array_map(function ($section) {
            return self::convertAIFormat($section);
        }, $sections);
    }

    /**
     * Full pipeline: convert AI format + normalize attributes.
     * Use this as the single entry point for AI-generated content.
     *
     * @param array $layout Layout with 'sections' key
     * @return array Fully normalized layout ready for JTB Renderer
     */
    public static function normalizeFromAI(array $layout): array
    {
        // Step 1: Convert AI format (content→children, flat→attrs)
        if (isset($layout['sections']) && is_array($layout['sections'])) {
            $layout['sections'] = self::convertAISections($layout['sections']);
        }

        // Step 2: Normalize attribute names and types
        return self::normalizeLayout($layout);
    }

    // =========================================================
    // MAIN NORMALIZATION METHOD
    // =========================================================

    /**
     * Normalize entire layout from AI
     *
     * @param array $layout Layout with sections array
     * @return array Normalized layout
     */
    public static function normalizeLayout(array $layout): array
    {
        if (!isset($layout['sections']) || !is_array($layout['sections'])) {
            return $layout;
        }

        $normalized = $layout;
        $normalized['sections'] = [];

        foreach ($layout['sections'] as $section) {
            $normalized['sections'][] = self::normalizeElement($section, 'section');
        }

        return $normalized;
    }

    /**
     * Normalize a single element (section, row, column, or module)
     *
     * @param array $element Element data
     * @param string|null $forcedType Force element type (optional)
     * @return array Normalized element
     */
    public static function normalizeElement(array $element, ?string $forcedType = null): array
    {
        $type = $forcedType ?? $element['type'] ?? 'unknown';

        // Normalize attrs
        if (isset($element['attrs']) && is_array($element['attrs'])) {
            $element['attrs'] = self::normalizeAttrs($element['attrs'], $type);
        } else {
            $element['attrs'] = [];
        }

        // Special handling for row - ensure columns attr is in attrs
        if ($type === 'row') {
            if (isset($element['columns']) && !isset($element['attrs']['columns'])) {
                $element['attrs']['columns'] = $element['columns'];
            }
        }

        // Recursively normalize children
        if (isset($element['children']) && is_array($element['children'])) {
            $childType = self::getChildType($type);
            $element['children'] = array_map(function($child) use ($childType) {
                return self::normalizeElement($child, $childType);
            }, $element['children']);
        }

        return $element;
    }

    /**
     * Normalize attributes for a specific module type
     *
     * @param array $attrs Raw attributes
     * @param string $moduleType Module type
     * @return array Normalized attributes
     */
    public static function normalizeAttrs(array $attrs, string $moduleType): array
    {
        $normalized = [];

        // Step 1: Map attribute names
        $mapped = self::mapAttributeNames($attrs, $moduleType);

        // Step 2: Convert flat spacing to arrays
        $mapped = self::convertFlatSpacingToArrays($mapped);

        // Step 3: Normalize types
        foreach ($mapped as $key => $value) {
            $normalized[$key] = self::normalizeValue($key, $value);
        }

        // Step 4: Validate and fix responsive keys
        $normalized = self::normalizeResponsiveKeys($normalized);

        return $normalized;
    }

    // =========================================================
    // ATTRIBUTE NAME MAPPING
    // =========================================================

    /**
     * Map AI attribute names to JTB expected names
     */
    private static function mapAttributeNames(array $attrs, string $moduleType): array
    {
        $result = [];

        // Get module-specific mappings
        $moduleMap = self::$attributeMaps[$moduleType] ?? [];
        $globalMap = self::$attributeMaps['_global'];

        foreach ($attrs as $key => $value) {
            // First check module-specific mapping
            if (isset($moduleMap[$key])) {
                $result[$moduleMap[$key]] = $value;
            }
            // Then check global mapping
            elseif (isset($globalMap[$key])) {
                $result[$globalMap[$key]] = $value;
            }
            // Keep original if no mapping found
            else {
                $result[$key] = $value;
            }
        }

        return $result;
    }

    // =========================================================
    // FLAT SPACING TO ARRAY CONVERSION
    // =========================================================

    /**
     * Convert flat spacing properties to array format
     * e.g., padding__top: 10 → padding: {top: 10, ...}
     */
    private static function convertFlatSpacingToArrays(array $attrs): array
    {
        $result = $attrs;

        foreach (self::$arrayProperties as $propName => $sides) {
            $arrayValue = [];
            $hasFlat = false;

            // Check for flat properties like padding__top
            foreach ($sides as $side) {
                $flatKey = $propName . '__' . $side;
                if (isset($attrs[$flatKey])) {
                    $arrayValue[$side] = self::normalizeNumeric($attrs[$flatKey]);
                    unset($result[$flatKey]);
                    $hasFlat = true;
                }
            }

            // If we found flat properties, create/merge array
            if ($hasFlat) {
                // Merge with existing array if present
                if (isset($result[$propName]) && is_array($result[$propName])) {
                    $result[$propName] = array_merge($result[$propName], $arrayValue);
                } else {
                    // Create new array with defaults for missing sides
                    $defaults = $propName === 'border_radius'
                        ? ['top_left' => 0, 'top_right' => 0, 'bottom_right' => 0, 'bottom_left' => 0]
                        : ['top' => 0, 'right' => 0, 'bottom' => 0, 'left' => 0];
                    $result[$propName] = array_merge($defaults, $arrayValue);
                }
            }

            // If property exists but is not array (single value), convert to array
            if (isset($result[$propName]) && !is_array($result[$propName])) {
                $singleValue = self::normalizeNumeric($result[$propName]);
                if ($propName === 'border_radius') {
                    $result[$propName] = [
                        'top_left' => $singleValue,
                        'top_right' => $singleValue,
                        'bottom_right' => $singleValue,
                        'bottom_left' => $singleValue,
                    ];
                } else {
                    $result[$propName] = [
                        'top' => $singleValue,
                        'right' => $singleValue,
                        'bottom' => $singleValue,
                        'left' => $singleValue,
                    ];
                }
            }
        }

        return $result;
    }

    // =========================================================
    // VALUE NORMALIZATION
    // =========================================================

    /**
     * Normalize a single value based on property type
     */
    private static function normalizeValue(string $key, $value)
    {
        // Handle null
        if ($value === null) {
            return null;
        }

        // Handle arrays (recursively normalize)
        if (is_array($value)) {
            return array_map(function($v) use ($key) {
                return self::normalizeValue($key, $v);
            }, $value);
        }

        // Check if this is a numeric property
        $baseKey = preg_replace('/__(?:tablet|phone|hover)$/', '', $key);
        if (in_array($baseKey, self::$numericProperties)) {
            return self::normalizeNumeric($value);
        }

        // Color values - ensure valid hex
        if (str_contains($key, 'color') || str_contains($key, 'colour')) {
            return self::normalizeColor($value);
        }

        // Font weight - ensure string
        if ($key === 'font_weight' || str_ends_with($key, '_font_weight')) {
            return (string) $value;
        }

        // Line height - can be number or string
        if ($key === 'line_height' || str_ends_with($key, '_line_height')) {
            return is_numeric($value) ? (float) $value : (string) $value;
        }

        return $value;
    }

    /**
     * Normalize numeric values (ensure integer/float, not string)
     */
    private static function normalizeNumeric($value)
    {
        if (is_numeric($value)) {
            // Return as int if whole number, float otherwise
            $floatVal = (float) $value;
            return ($floatVal == (int) $floatVal) ? (int) $floatVal : $floatVal;
        }

        // Try to extract number from string like "42px"
        if (is_string($value) && preg_match('/^([\d.]+)/', $value, $matches)) {
            $num = (float) $matches[1];
            return ($num == (int) $num) ? (int) $num : $num;
        }

        return 0; // Default to 0 if not parseable
    }

    /**
     * Normalize color values
     */
    private static function normalizeColor($value): string
    {
        if (!is_string($value)) {
            return '#000000';
        }

        $value = trim($value);

        // Already valid hex
        if (preg_match('/^#[0-9A-Fa-f]{3,8}$/', $value)) {
            return $value;
        }

        // Missing hash
        if (preg_match('/^[0-9A-Fa-f]{3,8}$/', $value)) {
            return '#' . $value;
        }

        // RGB/RGBA - keep as is
        if (preg_match('/^rgba?\s*\(/', $value)) {
            return $value;
        }

        // Named colors - keep as is
        $namedColors = ['black', 'white', 'red', 'green', 'blue', 'yellow', 'transparent', 'inherit'];
        if (in_array(strtolower($value), $namedColors)) {
            return strtolower($value);
        }

        return '#000000'; // Default
    }

    // =========================================================
    // RESPONSIVE KEY NORMALIZATION
    // =========================================================

    /**
     * Normalize responsive key suffixes
     * __mobile → __phone, __sm → __phone, etc.
     */
    private static function normalizeResponsiveKeys(array $attrs): array
    {
        $result = [];

        $responsiveMap = [
            '__mobile' => '__phone',
            '__sm' => '__phone',
            '__xs' => '__phone',
            '__md' => '__tablet',
            '__lg' => '__tablet',
        ];

        foreach ($attrs as $key => $value) {
            $normalizedKey = $key;

            foreach ($responsiveMap as $from => $to) {
                if (str_ends_with($key, $from)) {
                    $normalizedKey = substr($key, 0, -strlen($from)) . $to;
                    break;
                }
            }

            $result[$normalizedKey] = $value;
        }

        return $result;
    }

    // =========================================================
    // HELPERS
    // =========================================================

    /**
     * Get expected child type for a parent type
     */
    private static function getChildType(string $parentType): ?string
    {
        return match ($parentType) {
            'section' => 'row',
            'row' => 'column',
            'column' => null, // Modules can be any type
            default => null,
        };
    }

    /**
     * Debug: Log attribute transformation
     */
    public static function debugLog(string $moduleType, array $before, array $after): void
    {
        $logFile = '/tmp/jtb_normalizer_debug.log';
        $changes = [];

        foreach ($after as $key => $value) {
            if (!isset($before[$key]) || $before[$key] !== $value) {
                $changes[$key] = [
                    'before' => $before[$key] ?? '(not set)',
                    'after' => $value,
                ];
            }
        }

        if (!empty($changes)) {
            $log = date('Y-m-d H:i:s') . " [{$moduleType}] Changes:\n";
            foreach ($changes as $key => $change) {
                $beforeStr = is_array($change['before']) ? json_encode($change['before']) : $change['before'];
                $afterStr = is_array($change['after']) ? json_encode($change['after']) : $change['after'];
                $log .= "  {$key}: {$beforeStr} → {$afterStr}\n";
            }
            @file_put_contents($logFile, $log, FILE_APPEND);
        }
    }
}
