<?php
/**
 * JTB Attribute Converter
 *
 * Converts CSS properties to JTB module design attributes.
 * Maps CSS values to JTB's attribute format for all design options.
 *
 * @package JessieThemeBuilder\Parser
 */

namespace JessieThemeBuilder;

class JTB_Attribute_Converter
{
    private JTB_Style_Extractor $styleExtractor;

    public function __construct()
    {
        $this->styleExtractor = new JTB_Style_Extractor();
    }

    /**
     * Convert CSS styles to JTB module attributes
     *
     * @param array $styles Array of CSS properties and values
     * @param string $suffix Optional suffix for responsive/hover attributes (e.g., '__tablet', '__phone', '__hover')
     * @return array JTB module attributes
     */
    public function convert(array $styles, string $suffix = ''): array
    {
        $attrs = [];

        // Expand shorthand properties first
        $expandedStyles = $this->styleExtractor->expandAllShorthands($styles);

        // Convert each category
        $attrs = array_merge($attrs, $this->convertBackground($expandedStyles));
        $attrs = array_merge($attrs, $this->convertSpacing($expandedStyles));
        $attrs = array_merge($attrs, $this->convertBorder($expandedStyles));
        $attrs = array_merge($attrs, $this->convertBoxShadow($expandedStyles));
        $attrs = array_merge($attrs, $this->convertTypography($expandedStyles));
        $attrs = array_merge($attrs, $this->convertTransform($expandedStyles));
        $attrs = array_merge($attrs, $this->convertFilters($expandedStyles));
        $attrs = array_merge($attrs, $this->convertSizing($expandedStyles));
        $attrs = array_merge($attrs, $this->convertPosition($expandedStyles));
        $attrs = array_merge($attrs, $this->convertAnimation($expandedStyles));
        $attrs = array_merge($attrs, $this->convertMisc($expandedStyles));

        // Apply suffix if provided (for responsive/hover states)
        if (!empty($suffix)) {
            $suffixedAttrs = [];
            foreach ($attrs as $key => $value) {
                $suffixedAttrs[$key . $suffix] = $value;
            }
            return $suffixedAttrs;
        }

        return $attrs;
    }

    /**
     * Convert background styles
     */
    private function convertBackground(array $styles): array
    {
        $attrs = [];

        // Background color
        if (isset($styles['background-color'])) {
            $color = $styles['background-color'];
            if ($color !== 'transparent' && $color !== 'inherit') {
                $attrs['background_type'] = 'color';
                $attrs['background_color'] = $color;
            }
        }

        // Background image
        if (isset($styles['background-image'])) {
            $bgImage = $styles['background-image'];

            // Check for gradient
            if (preg_match('/linear-gradient\s*\(\s*(?:to\s+(\w+(?:\s+\w+)?)|(\d+)deg)?\s*,?\s*(.+)\)/i', $bgImage, $m)) {
                $attrs['background_type'] = 'gradient';
                $attrs['background_gradient_type'] = 'linear';

                // Parse direction
                if (!empty($m[2])) {
                    $attrs['background_gradient_direction'] = (int)$m[2];
                } elseif (!empty($m[1])) {
                    $directionMap = [
                        'top' => 0, 'right' => 90, 'bottom' => 180, 'left' => 270,
                        'top right' => 45, 'right top' => 45,
                        'bottom right' => 135, 'right bottom' => 135,
                        'bottom left' => 225, 'left bottom' => 225,
                        'top left' => 315, 'left top' => 315
                    ];
                    $attrs['background_gradient_direction'] = $directionMap[strtolower($m[1])] ?? 180;
                } else {
                    $attrs['background_gradient_direction'] = 180;
                }

                // Parse gradient stops
                if (!empty($m[3])) {
                    $attrs['background_gradient_stops'] = $this->parseGradientStops($m[3]);
                }
            } elseif (preg_match('/radial-gradient\s*\((.+)\)/i', $bgImage, $m)) {
                $attrs['background_type'] = 'gradient';
                $attrs['background_gradient_type'] = 'radial';
                $attrs['background_gradient_stops'] = $this->parseGradientStops($m[1]);
            } elseif (preg_match('/url\s*\(\s*[\'"]?([^\'")\s]+)[\'"]?\s*\)/i', $bgImage, $m)) {
                $attrs['background_type'] = 'image';
                $attrs['background_image'] = $m[1];
            }
        }

        // Background size
        if (isset($styles['background-size'])) {
            $attrs['background_size'] = $styles['background-size'];
        }

        // Background position
        if (isset($styles['background-position'])) {
            $attrs['background_position'] = $styles['background-position'];
        }

        // Background repeat
        if (isset($styles['background-repeat'])) {
            $attrs['background_repeat'] = $styles['background-repeat'];
        }

        // Parallax (fixed attachment)
        if (isset($styles['background-attachment']) && $styles['background-attachment'] === 'fixed') {
            $attrs['parallax'] = true;
        }

        // Background overlay (from custom property)
        if (isset($styles['--jtb-bg-overlay'])) {
            $attrs['background_image_overlay'] = $styles['--jtb-bg-overlay'];
        }

        return $attrs;
    }

    /**
     * Parse gradient color stops
     */
    private function parseGradientStops(string $stopsStr): array
    {
        $stops = [];

        // Split by commas not inside parentheses
        $parts = preg_split('/,(?![^(]*\))/', $stopsStr);

        foreach ($parts as $index => $part) {
            $part = trim($part);
            if (empty($part)) continue;

            // Skip gradient shape/position keywords
            if (preg_match('/^(circle|ellipse|closest|farthest|at\s)/i', $part)) {
                continue;
            }

            // Parse color and optional position
            if (preg_match('/^(.+?)\s+(\d+)%$/', $part, $m)) {
                $stops[] = [
                    'color' => trim($m[1]),
                    'position' => (int)$m[2]
                ];
            } else {
                // Just color, calculate position
                $stops[] = [
                    'color' => trim($part),
                    'position' => count($stops) === 0 ? 0 : 100
                ];
            }
        }

        // Ensure we have at least 2 stops with valid positions
        if (count($stops) === 2 && $stops[0]['position'] === 0 && $stops[1]['position'] === 0) {
            $stops[1]['position'] = 100;
        }

        return $stops;
    }

    /**
     * Convert spacing styles (margin/padding)
     */
    private function convertSpacing(array $styles): array
    {
        $attrs = [];

        // Margin - extract individual sides
        foreach (['top', 'right', 'bottom', 'left'] as $side) {
            $key = 'margin-' . $side;
            if (isset($styles[$key])) {
                $attrs['margin_' . $side] = $this->styleExtractor->toPixels($styles[$key]);
            }
        }

        // Padding - extract individual sides
        foreach (['top', 'right', 'bottom', 'left'] as $side) {
            $key = 'padding-' . $side;
            if (isset($styles[$key])) {
                $attrs['padding_' . $side] = $this->styleExtractor->toPixels($styles[$key]);
            }
        }

        // Gap (for flex/grid containers)
        if (isset($styles['gap'])) {
            $attrs['gap'] = $this->styleExtractor->toPixels($styles['gap']);
        }
        if (isset($styles['row-gap'])) {
            $attrs['row_gap'] = $this->styleExtractor->toPixels($styles['row-gap']);
        }
        if (isset($styles['column-gap'])) {
            $attrs['column_gap'] = $this->styleExtractor->toPixels($styles['column-gap']);
        }

        return $attrs;
    }

    /**
     * Extract four-sided values (margin, padding, border-width) as array
     * Used for border-width which stores as object
     */
    private function extractFourSidedValues(array $styles, string $property): array
    {
        $values = [];
        $hasValues = false;

        foreach (['top', 'right', 'bottom', 'left'] as $side) {
            $key = $property . '-' . $side;
            if (isset($styles[$key])) {
                $values[$side] = $this->styleExtractor->toPixels($styles[$key]);
                $hasValues = true;
            } else {
                $values[$side] = 0;
            }
        }

        return $hasValues ? $values : [];
    }

    /**
     * Convert border styles
     */
    private function convertBorder(array $styles): array
    {
        $attrs = [];

        // Border width
        $borderWidth = [];
        $hasBorderWidth = false;
        foreach (['top', 'right', 'bottom', 'left'] as $side) {
            $key = 'border-' . $side . '-width';
            if (isset($styles[$key])) {
                $borderWidth[$side] = $this->styleExtractor->toPixels($styles[$key]);
                $hasBorderWidth = true;
            } elseif (isset($styles['border-width'])) {
                $borderWidth[$side] = $this->styleExtractor->toPixels($styles['border-width']);
                $hasBorderWidth = true;
            } else {
                $borderWidth[$side] = 0;
            }
        }
        if ($hasBorderWidth) {
            $attrs['border_width'] = $borderWidth;
        }

        // Border style
        if (isset($styles['border-style'])) {
            $attrs['border_style'] = $styles['border-style'];
        }

        // Border color
        if (isset($styles['border-color'])) {
            $attrs['border_color'] = $styles['border-color'];
        }

        // Border radius
        $borderRadius = [];
        $hasBorderRadius = false;
        $radiusMap = [
            'top-left' => 'border-top-left-radius',
            'top-right' => 'border-top-right-radius',
            'bottom-right' => 'border-bottom-right-radius',
            'bottom-left' => 'border-bottom-left-radius'
        ];
        foreach ($radiusMap as $corner => $cssKey) {
            if (isset($styles[$cssKey])) {
                $borderRadius[str_replace('-', '_', $corner)] = $this->styleExtractor->toPixels($styles[$cssKey]);
                $hasBorderRadius = true;
            }
        }
        if ($hasBorderRadius) {
            $attrs['border_radius'] = $borderRadius;
        }

        return $attrs;
    }

    /**
     * Convert box shadow styles
     */
    private function convertBoxShadow(array $styles): array
    {
        $attrs = [];

        if (isset($styles['box-shadow-style'])) {
            if ($styles['box-shadow-style'] === 'none') {
                $attrs['box_shadow_style'] = 'none';
            } else {
                $attrs['box_shadow_style'] = 'custom';

                $shadowMap = [
                    'box-shadow-horizontal' => 'box_shadow_horizontal',
                    'box-shadow-vertical' => 'box_shadow_vertical',
                    'box-shadow-blur' => 'box_shadow_blur',
                    'box-shadow-spread' => 'box_shadow_spread',
                    'box-shadow-color' => 'box_shadow_color',
                    'box-shadow-inset' => 'box_shadow_inset'
                ];

                foreach ($shadowMap as $cssKey => $jtbKey) {
                    if (isset($styles[$cssKey])) {
                        $attrs[$jtbKey] = $styles[$cssKey];
                    }
                }
            }
        }

        return $attrs;
    }

    /**
     * Convert typography styles
     */
    private function convertTypography(array $styles): array
    {
        $attrs = [];

        // Direct mapping
        $typeMap = [
            'font-family' => 'font_family',
            'font-size' => 'font_size',
            'font-weight' => 'font_weight',
            'font-style' => 'font_style',
            'text-transform' => 'text_transform',
            'text-decoration' => 'text_decoration',
            'line-height' => 'line_height',
            'letter-spacing' => 'letter_spacing',
            'color' => 'text_color',
            'text-align' => 'text_align',
            'word-spacing' => 'word_spacing'
        ];

        foreach ($typeMap as $cssProperty => $jtbAttr) {
            if (!isset($styles[$cssProperty])) continue;

            $value = $styles[$cssProperty];

            switch ($cssProperty) {
                case 'font-family':
                    // Clean up font family - remove quotes and take first font
                    $value = preg_replace('/["\']/', '', $value);
                    $fonts = explode(',', $value);
                    $value = trim($fonts[0]);
                    break;

                case 'font-size':
                    $value = $this->styleExtractor->toPixels($value);
                    break;

                case 'letter-spacing':
                case 'word-spacing':
                    $value = $this->styleExtractor->toPixels($value);
                    break;

                case 'line-height':
                    // Convert to number if unitless
                    if (is_numeric($value)) {
                        $value = (float)$value;
                    } elseif (preg_match('/^([\d.]+)em$/i', $value, $m)) {
                        $value = (float)$m[1];
                    } elseif (preg_match('/^([\d.]+)px$/i', $value, $m)) {
                        // Keep as string with unit for CSS generation
                        $value = $m[1] . 'px';
                    }
                    break;

                case 'font-weight':
                    // Normalize to numeric value
                    $weightMap = [
                        'normal' => 400, 'bold' => 700,
                        'lighter' => 300, 'bolder' => 700
                    ];
                    if (isset($weightMap[strtolower($value)])) {
                        $value = $weightMap[strtolower($value)];
                    } else {
                        $value = (int)$value;
                    }
                    break;
            }

            $attrs[$jtbAttr] = $value;
        }

        // Text shadow
        if (isset($styles['text-shadow-style'])) {
            if ($styles['text-shadow-style'] === 'none') {
                $attrs['text_shadow_style'] = 'none';
            } else {
                $attrs['text_shadow_style'] = 'custom';

                $shadowMap = [
                    'text-shadow-horizontal' => 'text_shadow_horizontal',
                    'text-shadow-vertical' => 'text_shadow_vertical',
                    'text-shadow-blur' => 'text_shadow_blur',
                    'text-shadow-color' => 'text_shadow_color'
                ];

                foreach ($shadowMap as $cssKey => $jtbKey) {
                    if (isset($styles[$cssKey])) {
                        $attrs[$jtbKey] = $styles[$cssKey];
                    }
                }
            }
        }

        return $attrs;
    }

    /**
     * Convert transform styles
     */
    private function convertTransform(array $styles): array
    {
        $attrs = [];

        $transformMap = [
            'transform-scale' => 'transform_scale',
            'transform-scale-x' => 'transform_scale_x',
            'transform-scale-y' => 'transform_scale_y',
            'transform-rotate' => 'transform_rotate',
            'transform-skew-x' => 'transform_skew_x',
            'transform-skew-y' => 'transform_skew_y',
            'transform-translate-x' => 'transform_translate_x',
            'transform-translate-y' => 'transform_translate_y'
        ];

        foreach ($transformMap as $cssKey => $jtbKey) {
            if (isset($styles[$cssKey])) {
                $attrs[$jtbKey] = $styles[$cssKey];
            }
        }

        if (isset($styles['transform-origin'])) {
            $attrs['transform_origin'] = $styles['transform-origin'];
        }

        return $attrs;
    }

    /**
     * Convert filter styles
     */
    private function convertFilters(array $styles): array
    {
        $attrs = [];

        $filterMap = [
            'filter-hue-rotate' => 'filter_hue_rotate',
            'filter-saturate' => 'filter_saturate',
            'filter-brightness' => 'filter_brightness',
            'filter-contrast' => 'filter_contrast',
            'filter-invert' => 'filter_invert',
            'filter-sepia' => 'filter_sepia',
            'filter-blur' => 'filter_blur',
            'filter-grayscale' => 'filter_grayscale',
            'filter-opacity' => 'filter_opacity'
        ];

        foreach ($filterMap as $cssKey => $jtbKey) {
            if (isset($styles[$cssKey])) {
                $attrs[$jtbKey] = (float)$styles[$cssKey];
            }
        }

        return $attrs;
    }

    /**
     * Convert sizing styles
     */
    private function convertSizing(array $styles): array
    {
        $attrs = [];

        $sizeMap = [
            'width' => 'width',
            'max-width' => 'max_width',
            'min-width' => 'min_width',
            'height' => 'height',
            'max-height' => 'max_height',
            'min-height' => 'min_height'
        ];

        foreach ($sizeMap as $cssKey => $jtbKey) {
            if (isset($styles[$cssKey])) {
                $value = $styles[$cssKey];
                // Keep percentage values as-is, convert px
                if (strpos($value, '%') !== false) {
                    $attrs[$jtbKey] = $value;
                } elseif ($value === 'auto' || $value === 'inherit' || $value === 'initial') {
                    $attrs[$jtbKey] = $value;
                } else {
                    $attrs[$jtbKey] = $this->styleExtractor->toPixels($value);
                }
            }
        }

        // Overflow
        if (isset($styles['overflow'])) {
            $attrs['overflow'] = $styles['overflow'];
        }
        if (isset($styles['overflow-x'])) {
            $attrs['overflow_x'] = $styles['overflow-x'];
        }
        if (isset($styles['overflow-y'])) {
            $attrs['overflow_y'] = $styles['overflow-y'];
        }

        return $attrs;
    }

    /**
     * Convert position styles
     */
    private function convertPosition(array $styles): array
    {
        $attrs = [];

        if (isset($styles['position'])) {
            $position = $styles['position'];
            if (in_array($position, ['relative', 'absolute', 'fixed', 'sticky'])) {
                $attrs['position'] = $position;
            }
        }

        // Position offsets
        $offsetMap = [
            'top' => 'position_top',
            'right' => 'position_right',
            'bottom' => 'position_bottom',
            'left' => 'position_left'
        ];

        foreach ($offsetMap as $cssKey => $jtbKey) {
            if (isset($styles[$cssKey]) && $styles[$cssKey] !== 'auto') {
                $attrs[$jtbKey] = $this->styleExtractor->toPixels($styles[$cssKey]);
            }
        }

        // Z-index
        if (isset($styles['z-index'])) {
            $attrs['z_index'] = (int)$styles['z-index'];
        }

        return $attrs;
    }

    /**
     * Convert animation styles (from CSS custom properties)
     */
    private function convertAnimation(array $styles): array
    {
        $attrs = [];

        // Animation from custom properties
        if (isset($styles['--jtb-animation'])) {
            $attrs['animation_style'] = $styles['--jtb-animation'];
        }
        if (isset($styles['--jtb-animation-direction'])) {
            $attrs['animation_direction'] = $styles['--jtb-animation-direction'];
        }
        if (isset($styles['--jtb-animation-duration'])) {
            $attrs['animation_duration'] = (int)$styles['--jtb-animation-duration'];
        }
        if (isset($styles['--jtb-animation-delay'])) {
            $attrs['animation_delay'] = (int)$styles['--jtb-animation-delay'];
        }
        if (isset($styles['--jtb-animation-intensity'])) {
            $attrs['animation_intensity'] = (float)$styles['--jtb-animation-intensity'];
        }

        // CSS animation/transition
        if (isset($styles['transition'])) {
            $attrs['css_transition'] = $styles['transition'];
        }

        return $attrs;
    }

    /**
     * Convert miscellaneous styles
     */
    private function convertMisc(array $styles): array
    {
        $attrs = [];

        // Opacity
        if (isset($styles['opacity'])) {
            $opacity = (float)$styles['opacity'];
            $attrs['opacity'] = $opacity <= 1 ? $opacity * 100 : $opacity;
        }

        // Visibility
        if (isset($styles['visibility'])) {
            $attrs['visibility'] = $styles['visibility'];
        }

        // Display
        if (isset($styles['display'])) {
            $attrs['display'] = $styles['display'];
        }

        // Cursor
        if (isset($styles['cursor'])) {
            $attrs['cursor'] = $styles['cursor'];
        }

        // Object fit (for images)
        if (isset($styles['object-fit'])) {
            $attrs['object_fit'] = $styles['object-fit'];
        }

        // Object position
        if (isset($styles['object-position'])) {
            $attrs['object_position'] = $styles['object-position'];
        }

        // Vertical align
        if (isset($styles['vertical-align'])) {
            $attrs['vertical_align'] = $styles['vertical-align'];
        }

        return $attrs;
    }

    /**
     * Convert responsive styles from data attributes
     *
     * @param string $tabletStyles Tablet-specific styles string
     * @param string $phoneStyles Phone-specific styles string
     * @return array Responsive attributes with __tablet and __phone suffixes
     */
    public function convertResponsive(?string $tabletStyles, ?string $phoneStyles): array
    {
        $attrs = [];

        if ($tabletStyles) {
            $tabletParsed = $this->styleExtractor->parseStyleString($tabletStyles);
            $tabletConverted = $this->convert($tabletParsed);
            foreach ($tabletConverted as $key => $value) {
                $attrs[$key . '__tablet'] = $value;
            }
        }

        if ($phoneStyles) {
            $phoneParsed = $this->styleExtractor->parseStyleString($phoneStyles);
            $phoneConverted = $this->convert($phoneParsed);
            foreach ($phoneConverted as $key => $value) {
                $attrs[$key . '__phone'] = $value;
            }
        }

        return $attrs;
    }

    /**
     * Convert hover styles from data attribute
     *
     * @param string $hoverStyles Hover-specific styles string
     * @return array Hover attributes with __hover suffix
     */
    public function convertHover(?string $hoverStyles): array
    {
        $attrs = [];

        if ($hoverStyles) {
            $hoverParsed = $this->styleExtractor->parseStyleString($hoverStyles);
            $hoverConverted = $this->convert($hoverParsed);
            foreach ($hoverConverted as $key => $value) {
                $attrs[$key . '__hover'] = $value;
            }
        }

        return $attrs;
    }
}
