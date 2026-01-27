<?php
/**
 * JTB Style Extractor
 *
 * Extracts inline CSS styles from DOM elements and parses them into key-value pairs.
 * Handles shorthand properties expansion (margin, padding, border, background, etc.)
 *
 * @package JessieThemeBuilder\Parser
 */

namespace JessieThemeBuilder;

class JTB_Style_Extractor
{
    /**
     * Extract styles from a DOM element
     *
     * @param \DOMElement $node DOM element to extract styles from
     * @return array Associative array of CSS properties and values
     */
    public function extract(\DOMElement $node): array
    {
        $styles = [];

        // Get inline style attribute
        $styleAttr = $node->getAttribute('style');
        if ($styleAttr) {
            $styles = $this->parseStyleString($styleAttr);
        }

        // Extract CSS custom properties (--jtb-*)
        $customProps = $this->extractCssVars($styleAttr);
        $styles = array_merge($styles, $customProps);

        return $styles;
    }

    /**
     * Parse CSS style string into key-value array
     *
     * @param string $style CSS style string (e.g., "color: red; font-size: 16px;")
     * @return array Associative array of CSS properties and values
     */
    public function parseStyleString(string $style): array
    {
        $styles = [];

        // Normalize whitespace
        $style = str_replace(["\n", "\r", "\t"], ' ', $style);
        $style = preg_replace('/\s+/', ' ', trim($style));

        if (empty($style)) {
            return $styles;
        }

        // Split by semicolons (preserving function contents like url(), rgba())
        $declarations = $this->splitDeclarations($style);

        foreach ($declarations as $declaration) {
            $declaration = trim($declaration);
            if (empty($declaration)) continue;

            $colonPos = strpos($declaration, ':');
            if ($colonPos === false) continue;

            $property = trim(substr($declaration, 0, $colonPos));
            $value = trim(substr($declaration, $colonPos + 1));

            // Remove !important
            $value = str_ireplace('!important', '', $value);
            $value = trim($value);

            if (!empty($property) && $value !== '') {
                $styles[$property] = $value;
            }
        }

        return $styles;
    }

    /**
     * Split style string by semicolons, preserving function contents
     *
     * @param string $style CSS style string
     * @return array Array of individual declarations
     */
    private function splitDeclarations(string $style): array
    {
        $declarations = [];
        $current = '';
        $depth = 0;
        $length = strlen($style);

        for ($i = 0; $i < $length; $i++) {
            $char = $style[$i];

            if ($char === '(') {
                $depth++;
            } elseif ($char === ')') {
                $depth = max(0, $depth - 1);
            } elseif ($char === ';' && $depth === 0) {
                $declarations[] = $current;
                $current = '';
                continue;
            }

            $current .= $char;
        }

        // Don't forget the last declaration (may not end with semicolon)
        if (!empty(trim($current))) {
            $declarations[] = $current;
        }

        return $declarations;
    }

    /**
     * Extract content within balanced parentheses
     *
     * @param string $str String starting with '('
     * @return string|null Content including surrounding parens, or null if unbalanced
     */
    private function extractBalancedParens(string $str): ?string
    {
        if (empty($str) || $str[0] !== '(') {
            return null;
        }

        $depth = 0;
        $length = strlen($str);

        for ($i = 0; $i < $length; $i++) {
            $char = $str[$i];

            if ($char === '(') {
                $depth++;
            } elseif ($char === ')') {
                $depth--;
                if ($depth === 0) {
                    return substr($str, 0, $i + 1);
                }
            }
        }

        return null; // Unbalanced
    }

    /**
     * Extract CSS custom properties from style string
     *
     * @param string $style CSS style string
     * @return array Associative array of custom properties
     */
    private function extractCssVars(string $style): array
    {
        $vars = [];

        if (preg_match_all('/--jtb-([a-z0-9-]+):\s*([^;]+)/i', $style, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $varName = $match[1];
                $value = trim($match[2]);
                $vars['--jtb-' . $varName] = $value;
            }
        }

        return $vars;
    }

    /**
     * Expand shorthand CSS properties into individual properties
     *
     * @param string $property CSS property name
     * @param string $value CSS property value
     * @return array Expanded properties as associative array
     */
    public function expandShorthand(string $property, string $value): array
    {
        $property = strtolower($property);

        switch ($property) {
            case 'margin':
            case 'padding':
                return $this->expandSpacing($property, $value);

            case 'border':
                return $this->expandBorder($value);

            case 'border-width':
                return $this->expandFourSided('border', 'width', $value);

            case 'border-style':
                return $this->expandFourSided('border', 'style', $value);

            case 'border-color':
                return $this->expandFourSided('border', 'color', $value);

            case 'border-radius':
                return $this->expandBorderRadius($value);

            case 'background':
                return $this->expandBackground($value);

            case 'font':
                return $this->expandFont($value);

            case 'box-shadow':
                return $this->expandBoxShadow($value);

            case 'transform':
                return $this->expandTransform($value);

            case 'filter':
                return $this->expandFilter($value);

            case 'text-shadow':
                return $this->expandTextShadow($value);

            default:
                return [$property => $value];
        }
    }

    /**
     * Expand all shorthand properties in a styles array
     *
     * @param array $styles Array of CSS properties
     * @return array Expanded styles array
     */
    public function expandAllShorthands(array $styles): array
    {
        $expanded = [];

        foreach ($styles as $property => $value) {
            $exp = $this->expandShorthand($property, $value);
            $expanded = array_merge($expanded, $exp);
        }

        return $expanded;
    }

    /**
     * Expand spacing shorthand (margin/padding)
     *
     * @param string $property 'margin' or 'padding'
     * @param string $value CSS value (e.g., "10px 20px 30px 40px")
     * @return array Expanded to top/right/bottom/left
     */
    private function expandSpacing(string $property, string $value): array
    {
        $parts = preg_split('/\s+/', trim($value));
        $count = count($parts);

        // CSS shorthand rules:
        // 1 value: all sides
        // 2 values: top/bottom, left/right
        // 3 values: top, left/right, bottom
        // 4 values: top, right, bottom, left

        $top = $parts[0];
        $right = $count > 1 ? $parts[1] : $parts[0];
        $bottom = $count > 2 ? $parts[2] : $parts[0];
        $left = $count > 3 ? $parts[3] : ($count > 1 ? $parts[1] : $parts[0]);

        return [
            $property . '-top' => $top,
            $property . '-right' => $right,
            $property . '-bottom' => $bottom,
            $property . '-left' => $left
        ];
    }

    /**
     * Expand four-sided shorthand (border-width, border-style, border-color)
     *
     * @param string $prefix Property prefix (e.g., 'border')
     * @param string $suffix Property suffix (e.g., 'width')
     * @param string $value CSS value
     * @return array Expanded properties
     */
    private function expandFourSided(string $prefix, string $suffix, string $value): array
    {
        $parts = preg_split('/\s+/', trim($value));
        $count = count($parts);

        $top = $parts[0];
        $right = $count > 1 ? $parts[1] : $parts[0];
        $bottom = $count > 2 ? $parts[2] : $parts[0];
        $left = $count > 3 ? $parts[3] : ($count > 1 ? $parts[1] : $parts[0]);

        return [
            $prefix . '-top-' . $suffix => $top,
            $prefix . '-right-' . $suffix => $right,
            $prefix . '-bottom-' . $suffix => $bottom,
            $prefix . '-left-' . $suffix => $left
        ];
    }

    /**
     * Expand border shorthand
     *
     * @param string $value CSS border value (e.g., "1px solid #000")
     * @return array Expanded border properties
     */
    private function expandBorder(string $value): array
    {
        $expanded = [];

        // Parse: width style color
        // Example: "1px solid #333"
        $value = trim($value);

        // Extract width (number with unit)
        if (preg_match('/(\d+(?:\.\d+)?(?:px|em|rem|%|pt)?)/', $value, $m)) {
            $expanded['border-width'] = $m[1];
        }

        // Extract style
        $styles = ['none', 'hidden', 'dotted', 'dashed', 'solid', 'double', 'groove', 'ridge', 'inset', 'outset'];
        foreach ($styles as $style) {
            if (preg_match('/\b' . $style . '\b/i', $value)) {
                $expanded['border-style'] = $style;
                break;
            }
        }

        // Extract color (everything that's not width or style)
        $colorValue = preg_replace('/\d+(?:\.\d+)?(?:px|em|rem|%|pt)?/', '', $value);
        $colorValue = preg_replace('/\b(' . implode('|', $styles) . ')\b/i', '', $colorValue);
        $colorValue = trim($colorValue);
        if (!empty($colorValue)) {
            $expanded['border-color'] = $colorValue;
        }

        return $expanded;
    }

    /**
     * Expand border-radius shorthand
     *
     * @param string $value CSS border-radius value
     * @return array Expanded border-radius properties
     */
    private function expandBorderRadius(string $value): array
    {
        // Check for slash (horizontal/vertical radii)
        if (strpos($value, '/') !== false) {
            // Complex border-radius with elliptical corners - just store as-is
            return ['border-radius' => $value];
        }

        $parts = preg_split('/\s+/', trim($value));
        $count = count($parts);

        // CSS border-radius shorthand:
        // 1 value: all corners
        // 2 values: top-left/bottom-right, top-right/bottom-left
        // 3 values: top-left, top-right/bottom-left, bottom-right
        // 4 values: top-left, top-right, bottom-right, bottom-left

        $topLeft = $parts[0];
        $topRight = $count > 1 ? $parts[1] : $parts[0];
        $bottomRight = $count > 2 ? $parts[2] : $parts[0];
        $bottomLeft = $count > 3 ? $parts[3] : ($count > 1 ? $parts[1] : $parts[0]);

        return [
            'border-top-left-radius' => $topLeft,
            'border-top-right-radius' => $topRight,
            'border-bottom-right-radius' => $bottomRight,
            'border-bottom-left-radius' => $bottomLeft
        ];
    }

    /**
     * Expand background shorthand
     *
     * @param string $value CSS background value
     * @return array Expanded background properties
     */
    private function expandBackground(string $value): array
    {
        $expanded = [];
        $value = trim($value);

        // Check for gradient (handle nested parentheses like rgba() inside gradient)
        if (preg_match('/(linear-gradient|radial-gradient|conic-gradient)\s*\(/', $value, $m, PREG_OFFSET_CAPTURE)) {
            $gradientStart = $m[0][1];
            $gradientContent = $this->extractBalancedParens(substr($value, $gradientStart + strlen($m[1][0])));
            if ($gradientContent !== null) {
                $expanded['background-image'] = $m[1][0] . $gradientContent;
            }
        }

        // Check for url()
        if (preg_match('/url\s*\(\s*[\'"]?([^\'")\s]+)[\'"]?\s*\)/i', $value, $m)) {
            $expanded['background-image'] = 'url(' . $m[1] . ')';
        }

        // Check for color (hex, rgb, rgba, hsl, named colors)
        // This should come after gradient/url extraction
        $colorPattern = '/(#[0-9a-fA-F]{3,8}|rgba?\s*\([^)]+\)|hsla?\s*\([^)]+\)|\b(?:transparent|inherit|initial|currentColor|' .
                        'aliceblue|antiquewhite|aqua|aquamarine|azure|beige|bisque|black|blanchedalmond|blue|blueviolet|brown|' .
                        'burlywood|cadetblue|chartreuse|chocolate|coral|cornflowerblue|cornsilk|crimson|cyan|darkblue|darkcyan|' .
                        'darkgoldenrod|darkgray|darkgreen|darkgrey|darkkhaki|darkmagenta|darkolivegreen|darkorange|darkorchid|' .
                        'darkred|darksalmon|darkseagreen|darkslateblue|darkslategray|darkslategrey|darkturquoise|darkviolet|' .
                        'deeppink|deepskyblue|dimgray|dimgrey|dodgerblue|firebrick|floralwhite|forestgreen|fuchsia|gainsboro|' .
                        'ghostwhite|gold|goldenrod|gray|green|greenyellow|grey|honeydew|hotpink|indianred|indigo|ivory|khaki|' .
                        'lavender|lavenderblush|lawngreen|lemonchiffon|lightblue|lightcoral|lightcyan|lightgoldenrodyellow|' .
                        'lightgray|lightgreen|lightgrey|lightpink|lightsalmon|lightseagreen|lightskyblue|lightslategray|' .
                        'lightslategrey|lightsteelblue|lightyellow|lime|limegreen|linen|magenta|maroon|mediumaquamarine|' .
                        'mediumblue|mediumorchid|mediumpurple|mediumseagreen|mediumslateblue|mediumspringgreen|mediumturquoise|' .
                        'mediumvioletred|midnightblue|mintcream|mistyrose|moccasin|navajowhite|navy|oldlace|olive|olivedrab|' .
                        'orange|orangered|orchid|palegoldenrod|palegreen|paleturquoise|palevioletred|papayawhip|peachpuff|peru|' .
                        'pink|plum|powderblue|purple|rebeccapurple|red|rosybrown|royalblue|saddlebrown|salmon|sandybrown|' .
                        'seagreen|seashell|sienna|silver|skyblue|slateblue|slategray|slategrey|snow|springgreen|steelblue|' .
                        'tan|teal|thistle|tomato|turquoise|violet|wheat|white|whitesmoke|yellow|yellowgreen)\b)/i';

        if (preg_match($colorPattern, $value, $m)) {
            // Only set if not already a gradient
            if (!isset($expanded['background-image']) || strpos($expanded['background-image'], 'gradient') === false) {
                $expanded['background-color'] = $m[1];
            }
        }

        // Check for size keywords
        if (preg_match('/\b(cover|contain|auto)\b/i', $value, $m)) {
            $expanded['background-size'] = strtolower($m[1]);
        }

        // Check for position keywords
        $positionKeywords = ['center', 'top', 'bottom', 'left', 'right'];
        $positions = [];
        foreach ($positionKeywords as $pos) {
            if (preg_match('/\b' . $pos . '\b/i', $value)) {
                $positions[] = $pos;
            }
        }
        if (!empty($positions)) {
            $expanded['background-position'] = implode(' ', array_slice($positions, 0, 2));
        }

        // Check for repeat
        if (preg_match('/\b(no-repeat|repeat-x|repeat-y|repeat)\b/i', $value, $m)) {
            $expanded['background-repeat'] = strtolower($m[1]);
        }

        // Check for attachment
        if (preg_match('/\b(fixed|scroll|local)\b/i', $value, $m)) {
            $expanded['background-attachment'] = strtolower($m[1]);
        }

        return $expanded;
    }

    /**
     * Expand font shorthand
     *
     * @param string $value CSS font value
     * @return array Expanded font properties
     */
    private function expandFont(string $value): array
    {
        $expanded = [];
        $value = trim($value);

        // Font shorthand: font-style font-variant font-weight font-stretch font-size/line-height font-family
        // Most are optional except font-size and font-family

        // Extract font-family (everything after the last number/unit)
        if (preg_match('/[\d.]+(?:px|em|rem|%|pt|vw|vh)\s*(?:\/\s*[\d.]+(?:px|em|rem|%)?)?(.+)$/i', $value, $m)) {
            $family = trim($m[1]);
            if (!empty($family)) {
                $expanded['font-family'] = $family;
            }
        }

        // Extract font-size (and line-height if present)
        if (preg_match('/([\d.]+(?:px|em|rem|%|pt|vw|vh))(?:\s*\/\s*([\d.]+(?:px|em|rem|%)?))?/i', $value, $m)) {
            $expanded['font-size'] = $m[1];
            if (isset($m[2]) && !empty($m[2])) {
                $expanded['line-height'] = $m[2];
            }
        }

        // Extract font-weight
        if (preg_match('/\b(100|200|300|400|500|600|700|800|900|normal|bold|bolder|lighter)\b/i', $value, $m)) {
            $expanded['font-weight'] = strtolower($m[1]);
        }

        // Extract font-style
        if (preg_match('/\b(italic|oblique)\b/i', $value, $m)) {
            $expanded['font-style'] = strtolower($m[1]);
        }

        return $expanded;
    }

    /**
     * Expand box-shadow
     *
     * @param string $value CSS box-shadow value
     * @return array Expanded box-shadow properties
     */
    private function expandBoxShadow(string $value): array
    {
        $value = trim($value);

        if ($value === 'none') {
            return ['box-shadow-style' => 'none'];
        }

        $expanded = ['box-shadow-style' => 'custom'];

        // Check for inset
        $inset = false;
        if (preg_match('/\binset\b/i', $value)) {
            $inset = true;
            $value = preg_replace('/\binset\b/i', '', $value);
            $expanded['box-shadow-inset'] = true;
        }

        // Extract color (at start or end)
        $color = 'rgba(0,0,0,0.3)';
        if (preg_match('/(#[0-9a-fA-F]{3,8}|rgba?\s*\([^)]+\)|hsla?\s*\([^)]+\))/i', $value, $m)) {
            $color = $m[1];
            $value = str_replace($m[1], '', $value);
        }
        $expanded['box-shadow-color'] = $color;

        // Extract numeric values (h-offset v-offset blur spread)
        if (preg_match_all('/(-?[\d.]+)(?:px)?/i', $value, $matches)) {
            $values = array_map('floatval', $matches[1]);

            $expanded['box-shadow-horizontal'] = $values[0] ?? 0;
            $expanded['box-shadow-vertical'] = $values[1] ?? 0;
            $expanded['box-shadow-blur'] = $values[2] ?? 0;
            $expanded['box-shadow-spread'] = $values[3] ?? 0;
        }

        return $expanded;
    }

    /**
     * Expand transform
     *
     * @param string $value CSS transform value
     * @return array Expanded transform properties
     */
    private function expandTransform(string $value): array
    {
        $expanded = [];
        $value = trim($value);

        if ($value === 'none') {
            return ['transform' => 'none'];
        }

        // Parse scale
        if (preg_match('/scale\s*\(\s*([0-9.]+)(?:\s*,\s*([0-9.]+))?\s*\)/i', $value, $m)) {
            $expanded['transform-scale'] = (float)$m[1] * 100;
            if (isset($m[2])) {
                $expanded['transform-scale-x'] = (float)$m[1] * 100;
                $expanded['transform-scale-y'] = (float)$m[2] * 100;
            }
        }
        if (preg_match('/scaleX\s*\(\s*([0-9.]+)\s*\)/i', $value, $m)) {
            $expanded['transform-scale-x'] = (float)$m[1] * 100;
        }
        if (preg_match('/scaleY\s*\(\s*([0-9.]+)\s*\)/i', $value, $m)) {
            $expanded['transform-scale-y'] = (float)$m[1] * 100;
        }

        // Parse rotate
        if (preg_match('/rotate\s*\(\s*(-?[0-9.]+)(?:deg)?\s*\)/i', $value, $m)) {
            $expanded['transform-rotate'] = (float)$m[1];
        }

        // Parse skew
        if (preg_match('/skew\s*\(\s*(-?[0-9.]+)(?:deg)?(?:\s*,\s*(-?[0-9.]+)(?:deg)?)?\s*\)/i', $value, $m)) {
            $expanded['transform-skew-x'] = (float)$m[1];
            if (isset($m[2])) {
                $expanded['transform-skew-y'] = (float)$m[2];
            }
        }
        if (preg_match('/skewX\s*\(\s*(-?[0-9.]+)(?:deg)?\s*\)/i', $value, $m)) {
            $expanded['transform-skew-x'] = (float)$m[1];
        }
        if (preg_match('/skewY\s*\(\s*(-?[0-9.]+)(?:deg)?\s*\)/i', $value, $m)) {
            $expanded['transform-skew-y'] = (float)$m[1];
        }

        // Parse translate
        if (preg_match('/translate\s*\(\s*(-?[0-9.]+)(?:px)?(?:\s*,\s*(-?[0-9.]+)(?:px)?)?\s*\)/i', $value, $m)) {
            $expanded['transform-translate-x'] = (float)$m[1];
            if (isset($m[2])) {
                $expanded['transform-translate-y'] = (float)$m[2];
            }
        }
        if (preg_match('/translateX\s*\(\s*(-?[0-9.]+)(?:px)?\s*\)/i', $value, $m)) {
            $expanded['transform-translate-x'] = (float)$m[1];
        }
        if (preg_match('/translateY\s*\(\s*(-?[0-9.]+)(?:px)?\s*\)/i', $value, $m)) {
            $expanded['transform-translate-y'] = (float)$m[1];
        }

        return $expanded;
    }

    /**
     * Expand filter
     *
     * @param string $value CSS filter value
     * @return array Expanded filter properties
     */
    private function expandFilter(string $value): array
    {
        $expanded = [];
        $value = trim($value);

        if ($value === 'none') {
            return ['filter' => 'none'];
        }

        $filterMap = [
            'hue-rotate' => ['pattern' => '/hue-rotate\s*\(\s*(-?[0-9.]+)(?:deg)?\s*\)/i', 'key' => 'filter-hue-rotate'],
            'saturate' => ['pattern' => '/saturate\s*\(\s*([0-9.]+)%?\s*\)/i', 'key' => 'filter-saturate'],
            'brightness' => ['pattern' => '/brightness\s*\(\s*([0-9.]+)%?\s*\)/i', 'key' => 'filter-brightness'],
            'contrast' => ['pattern' => '/contrast\s*\(\s*([0-9.]+)%?\s*\)/i', 'key' => 'filter-contrast'],
            'invert' => ['pattern' => '/invert\s*\(\s*([0-9.]+)%?\s*\)/i', 'key' => 'filter-invert'],
            'sepia' => ['pattern' => '/sepia\s*\(\s*([0-9.]+)%?\s*\)/i', 'key' => 'filter-sepia'],
            'blur' => ['pattern' => '/blur\s*\(\s*([0-9.]+)(?:px)?\s*\)/i', 'key' => 'filter-blur'],
            'grayscale' => ['pattern' => '/grayscale\s*\(\s*([0-9.]+)%?\s*\)/i', 'key' => 'filter-grayscale'],
            'opacity' => ['pattern' => '/opacity\s*\(\s*([0-9.]+)%?\s*\)/i', 'key' => 'filter-opacity'],
            'drop-shadow' => ['pattern' => '/drop-shadow\s*\(([^)]+)\)/i', 'key' => 'filter-drop-shadow']
        ];

        foreach ($filterMap as $name => $config) {
            if (preg_match($config['pattern'], $value, $m)) {
                $expanded[$config['key']] = $m[1];
            }
        }

        return $expanded;
    }

    /**
     * Expand text-shadow
     *
     * @param string $value CSS text-shadow value
     * @return array Expanded text-shadow properties
     */
    private function expandTextShadow(string $value): array
    {
        $value = trim($value);

        if ($value === 'none') {
            return ['text-shadow-style' => 'none'];
        }

        $expanded = ['text-shadow-style' => 'custom'];

        // Extract color
        $color = 'rgba(0,0,0,0.3)';
        if (preg_match('/(#[0-9a-fA-F]{3,8}|rgba?\s*\([^)]+\)|hsla?\s*\([^)]+\))/i', $value, $m)) {
            $color = $m[1];
            $value = str_replace($m[1], '', $value);
        }
        $expanded['text-shadow-color'] = $color;

        // Extract numeric values (h-offset v-offset blur)
        if (preg_match_all('/(-?[\d.]+)(?:px)?/i', $value, $matches)) {
            $values = array_map('floatval', $matches[1]);

            $expanded['text-shadow-horizontal'] = $values[0] ?? 0;
            $expanded['text-shadow-vertical'] = $values[1] ?? 0;
            $expanded['text-shadow-blur'] = $values[2] ?? 0;
        }

        return $expanded;
    }

    /**
     * Parse a single CSS value and extract numeric value with unit
     *
     * @param string $value CSS value (e.g., "16px", "1.5em", "100%")
     * @return array ['value' => float, 'unit' => string]
     */
    public function parseValueWithUnit(string $value): array
    {
        $value = trim($value);

        if (preg_match('/^(-?[\d.]+)(px|em|rem|%|vw|vh|vmin|vmax|pt|pc|in|cm|mm)?$/i', $value, $m)) {
            return [
                'value' => (float)$m[1],
                'unit' => isset($m[2]) ? strtolower($m[2]) : ''
            ];
        }

        return ['value' => 0, 'unit' => ''];
    }

    /**
     * Convert CSS value to pixels (approximation)
     *
     * @param string $value CSS value
     * @param int $baseFontSize Base font size for em/rem conversion (default 16)
     * @return int Pixel value
     */
    public function toPixels(string $value, int $baseFontSize = 16): int
    {
        $parsed = $this->parseValueWithUnit($value);

        switch ($parsed['unit']) {
            case 'px':
            case '':
                return (int)$parsed['value'];
            case 'em':
            case 'rem':
                return (int)($parsed['value'] * $baseFontSize);
            case 'pt':
                return (int)($parsed['value'] * 1.333);
            case '%':
                // Can't convert without context
                return (int)$parsed['value'];
            default:
                return (int)$parsed['value'];
        }
    }
}
