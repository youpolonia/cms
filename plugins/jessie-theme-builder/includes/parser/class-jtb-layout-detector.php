<?php
/**
 * JTB Layout Detector
 *
 * Detects layout structures in HTML (flexbox, grid, sections, rows, columns)
 * and converts them to JTB structure format.
 *
 * @package JessieThemeBuilder
 */

namespace JessieThemeBuilder;

class JTB_Layout_Detector
{
    /**
     * Section-like HTML tags
     */
    private const SECTION_TAGS = ['section', 'header', 'footer', 'main', 'article', 'aside', 'nav'];

    /**
     * Section-like CSS class patterns
     */
    private const SECTION_CLASSES = [
        'section', 'container', 'wrapper', 'hero', 'banner', 'block',
        'area', 'zone', 'region', 'panel', 'segment', 'part'
    ];

    /**
     * Row-like CSS class patterns
     */
    private const ROW_CLASSES = [
        'row', 'flex', 'grid', 'columns', 'd-flex', 'flexbox',
        'layout', 'items', 'group', 'list'
    ];

    /**
     * Column-like CSS class patterns
     */
    private const COLUMN_CLASSES = [
        'col', 'column', 'cell', 'grid-item', 'flex-item',
        'item', 'card', 'box', 'tile'
    ];

    /**
     * Check if element is section-like
     *
     * @param \DOMElement $node DOM element to check
     * @return bool True if element should be treated as a section
     */
    public static function isSectionLike(\DOMElement $node): bool
    {
        $tagName = strtolower($node->nodeName);

        // Semantic section elements
        if (in_array($tagName, self::SECTION_TAGS)) {
            return true;
        }

        // Check for data-jtb-module="section"
        if ($node->getAttribute('data-jtb-module') === 'section') {
            return true;
        }

        // Div with section-like classes
        if ($tagName === 'div') {
            $class = strtolower($node->getAttribute('class'));

            foreach (self::SECTION_CLASSES as $sectionClass) {
                if (strpos($class, $sectionClass) !== false) {
                    return true;
                }
            }

            // Check for section-like ID
            $id = strtolower($node->getAttribute('id'));
            foreach (self::SECTION_CLASSES as $sectionClass) {
                if (strpos($id, $sectionClass) !== false) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Check if element is row-like (flex/grid container)
     *
     * @param \DOMElement $node DOM element to check
     * @return bool True if element should be treated as a row
     */
    public static function isRowLike(\DOMElement $node): bool
    {
        // Check for data-jtb-module="row"
        if ($node->getAttribute('data-jtb-module') === 'row') {
            return true;
        }

        $style = $node->getAttribute('style');
        $class = strtolower($node->getAttribute('class'));

        // Check for flex display
        if (preg_match('/display\s*:\s*flex/i', $style)) {
            // Check flex-direction - only row if horizontal
            if (!preg_match('/flex-direction\s*:\s*column/i', $style)) {
                return true;
            }
        }

        // Check for grid display
        if (preg_match('/display\s*:\s*grid/i', $style)) {
            // Check if grid-template-columns is set (horizontal layout)
            if (preg_match('/grid-template-columns/i', $style)) {
                return true;
            }
        }

        // Check class names
        foreach (self::ROW_CLASSES as $rowClass) {
            if (preg_match('/\b' . preg_quote($rowClass, '/') . '\b/', $class)) {
                return true;
            }
        }

        // Check for Bootstrap-like row class
        if (preg_match('/\brow\b/', $class)) {
            return true;
        }

        return false;
    }

    /**
     * Check if element is column-like
     *
     * @param \DOMElement $node DOM element to check
     * @return bool True if element should be treated as a column
     */
    public static function isColumnLike(\DOMElement $node): bool
    {
        // Check for data-jtb-module="column"
        if ($node->getAttribute('data-jtb-module') === 'column') {
            return true;
        }

        $class = strtolower($node->getAttribute('class'));
        $style = $node->getAttribute('style');

        // Check class names
        foreach (self::COLUMN_CLASSES as $colClass) {
            if (preg_match('/\b' . preg_quote($colClass, '/') . '/', $class)) {
                return true;
            }
        }

        // Check for Bootstrap-like column classes
        if (preg_match('/\bcol(-\d+|-sm|-md|-lg|-xl)?(-\d+)?\b/', $class)) {
            return true;
        }

        // Check for Tailwind-like width classes
        if (preg_match('/\bw-(\d+\/\d+|full|auto)\b/', $class)) {
            return true;
        }

        // Check for flex-basis or explicit width percentage in style
        if (preg_match('/flex(-basis)?\s*:\s*\d/', $style) ||
            preg_match('/width\s*:\s*\d+%/', $style)) {
            return true;
        }

        return false;
    }

    /**
     * Detect column structure from a row element
     *
     * @param \DOMElement $node Row element
     * @return string|null JTB column structure string (e.g., "1_3,2_3") or null
     */
    public static function detectColumnStructure(\DOMElement $node): ?string
    {
        // Check for explicit data attribute
        $explicit = $node->getAttribute('data-jtb-attr-columns');
        if (!empty($explicit)) {
            return $explicit;
        }

        $children = self::getElementChildren($node);
        $count = count($children);

        if ($count === 0) return '1';
        if ($count === 1) return '1';

        // Try to detect from child widths
        $widths = [];
        foreach ($children as $child) {
            $width = self::detectChildWidth($child, $count);
            if ($width !== null) {
                $widths[] = $width;
            }
        }

        // If we have width info for all children, convert to JTB format
        if (count($widths) === $count) {
            return self::widthsToColumnStructure($widths);
        }

        // Fall back to equal columns
        return self::equalColumnsStructure($count);
    }

    /**
     * Detect child element width
     *
     * @param \DOMElement $node Child element
     * @param int $siblingCount Total number of siblings
     * @return float|null Width as percentage, or null if not detected
     */
    private static function detectChildWidth(\DOMElement $node, int $siblingCount): ?float
    {
        // Check for explicit data attribute
        $explicit = $node->getAttribute('data-jtb-attr-width');
        if (!empty($explicit)) {
            return self::fractionToPercentage($explicit);
        }

        $style = $node->getAttribute('style');
        $class = strtolower($node->getAttribute('class'));

        // Check inline style width
        if (preg_match('/width\s*:\s*([0-9.]+)%/', $style, $m)) {
            return (float)$m[1];
        }

        // Check flex-basis percentage
        if (preg_match('/flex(-basis)?\s*:\s*([0-9.]+)%/', $style, $m)) {
            return (float)$m[2];
        }

        // Check flex shorthand (e.g., flex: 1 1 33.33%)
        if (preg_match('/flex\s*:\s*[\d.]+\s+[\d.]+\s+([\d.]+)%/', $style, $m)) {
            return (float)$m[1];
        }

        // Check Bootstrap-like classes (col-1 through col-12)
        if (preg_match('/\bcol-(\d+)\b/', $class, $m)) {
            return ((int)$m[1] / 12) * 100;
        }

        // Check responsive Bootstrap classes
        foreach (['sm', 'md', 'lg', 'xl', 'xxl'] as $breakpoint) {
            if (preg_match('/\bcol-' . $breakpoint . '-(\d+)\b/', $class, $m)) {
                return ((int)$m[1] / 12) * 100;
            }
        }

        // Check Tailwind-like fraction classes
        $tailwindMap = [
            'w-1/2' => 50, 'w-1/3' => 33.33, 'w-2/3' => 66.66,
            'w-1/4' => 25, 'w-3/4' => 75,
            'w-1/5' => 20, 'w-2/5' => 40, 'w-3/5' => 60, 'w-4/5' => 80,
            'w-1/6' => 16.66, 'w-5/6' => 83.33,
            'w-full' => 100
        ];
        foreach ($tailwindMap as $twClass => $percentage) {
            if (strpos($class, $twClass) !== false) {
                return $percentage;
            }
        }

        // Check CSS grid - if parent is grid, look for grid-column span
        if (preg_match('/grid-column\s*:\s*span\s*(\d+)/', $style, $m)) {
            // Assume 12-column grid
            return ((int)$m[1] / 12) * 100;
        }

        return null;
    }

    /**
     * Convert fraction string to percentage
     *
     * @param string $fraction Fraction like "1_3" or "2_3"
     * @return float Percentage value
     */
    private static function fractionToPercentage(string $fraction): float
    {
        $fractionMap = [
            '1' => 100,
            '1_2' => 50,
            '1_3' => 33.33,
            '2_3' => 66.66,
            '1_4' => 25,
            '3_4' => 75,
            '1_5' => 20,
            '2_5' => 40,
            '3_5' => 60,
            '4_5' => 80,
            '1_6' => 16.66,
            '5_6' => 83.33
        ];

        return $fractionMap[$fraction] ?? 50;
    }

    /**
     * Convert percentage array to JTB column structure string
     *
     * @param array $widths Array of width percentages
     * @return string JTB column structure (e.g., "1_3,2_3")
     */
    private static function widthsToColumnStructure(array $widths): string
    {
        $fractions = [];

        foreach ($widths as $width) {
            $fractions[] = self::percentageToFraction($width);
        }

        return implode(',', $fractions);
    }

    /**
     * Convert percentage to JTB fraction format
     *
     * @param float $percentage Width percentage
     * @return string JTB fraction (e.g., "1_3")
     */
    private static function percentageToFraction(float $percentage): string
    {
        $fractionMap = [
            100 => '1',
            50 => '1_2',
            33.33 => '1_3',
            33.34 => '1_3',
            33 => '1_3',
            66.66 => '2_3',
            66.67 => '2_3',
            67 => '2_3',
            25 => '1_4',
            75 => '3_4',
            20 => '1_5',
            40 => '2_5',
            60 => '3_5',
            80 => '4_5',
            16.66 => '1_6',
            16.67 => '1_6',
            17 => '1_6',
            83.33 => '5_6',
            83.34 => '5_6',
            83 => '5_6'
        ];

        // Find closest match
        $closest = '1_2';
        $minDiff = PHP_FLOAT_MAX;

        foreach ($fractionMap as $percent => $fraction) {
            $diff = abs($percentage - $percent);
            if ($diff < $minDiff) {
                $minDiff = $diff;
                $closest = $fraction;
            }
        }

        return $closest;
    }

    /**
     * Generate equal columns structure
     *
     * @param int $count Number of columns
     * @return string JTB column structure
     */
    private static function equalColumnsStructure(int $count): string
    {
        $structures = [
            1 => '1',
            2 => '1_2,1_2',
            3 => '1_3,1_3,1_3',
            4 => '1_4,1_4,1_4,1_4',
            5 => '1_5,1_5,1_5,1_5,1_5',
            6 => '1_6,1_6,1_6,1_6,1_6,1_6'
        ];

        if (isset($structures[$count])) {
            return $structures[$count];
        }

        // For more than 6, use equal fractions
        $fractions = array_fill(0, min($count, 12), '1_' . $count);
        return implode(',', $fractions);
    }

    /**
     * Get element children (element nodes only, skip text/comments)
     *
     * @param \DOMElement $node Parent element
     * @return array Array of DOMElement children
     */
    public static function getElementChildren(\DOMElement $node): array
    {
        $children = [];
        foreach ($node->childNodes as $child) {
            if ($child->nodeType === XML_ELEMENT_NODE) {
                $children[] = $child;
            }
        }
        return $children;
    }

    /**
     * Check if element has significant content (not just whitespace)
     *
     * @param \DOMElement $node DOM element
     * @return bool True if element has content
     */
    public static function hasSignificantContent(\DOMElement $node): bool
    {
        // Check for child elements
        foreach ($node->childNodes as $child) {
            if ($child->nodeType === XML_ELEMENT_NODE) {
                return true;
            }
            if ($child->nodeType === XML_TEXT_NODE) {
                if (!empty(trim($child->textContent))) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Detect if element is a fullwidth section
     *
     * @param \DOMElement $node DOM element
     * @return bool True if section should be fullwidth
     */
    public static function isFullwidthElement(\DOMElement $node): bool
    {
        $class = strtolower($node->getAttribute('class'));
        $style = $node->getAttribute('style');

        // Check for fullwidth indicators in class
        $fullwidthClasses = ['full-width', 'fullwidth', 'full-bleed', 'w-full', 'w-screen'];
        foreach ($fullwidthClasses as $fwClass) {
            if (strpos($class, $fwClass) !== false) {
                return true;
            }
        }

        // Check for 100vw width
        if (preg_match('/width\s*:\s*100vw/', $style)) {
            return true;
        }

        // Check for explicit data attribute
        if ($node->getAttribute('data-jtb-attr-fullwidth') === 'true') {
            return true;
        }

        return false;
    }

    /**
     * Detect inner width constraint
     *
     * @param \DOMElement $node Section element
     * @return int|null Inner width in pixels, or null for default
     */
    public static function detectInnerWidth(\DOMElement $node): ?int
    {
        $style = $node->getAttribute('style');
        $class = strtolower($node->getAttribute('class'));

        // Check explicit data attribute
        $explicit = $node->getAttribute('data-jtb-attr-inner_width');
        if (!empty($explicit)) {
            return (int)$explicit;
        }

        // Check for max-width in inner container
        $children = self::getElementChildren($node);
        foreach ($children as $child) {
            $childClass = strtolower($child->getAttribute('class'));
            $childStyle = $child->getAttribute('style');

            // Look for container/inner wrapper
            if (strpos($childClass, 'container') !== false ||
                strpos($childClass, 'inner') !== false ||
                strpos($childClass, 'content') !== false) {

                if (preg_match('/max-width\s*:\s*(\d+)px/', $childStyle, $m)) {
                    return (int)$m[1];
                }
            }
        }

        // Check for common container widths in style
        if (preg_match('/max-width\s*:\s*(\d+)px/', $style, $m)) {
            return (int)$m[1];
        }

        // Check for Bootstrap-like container classes
        $containerWidths = [
            'container-sm' => 540,
            'container-md' => 720,
            'container-lg' => 960,
            'container-xl' => 1140,
            'container-xxl' => 1320,
            'container' => 1200
        ];
        foreach ($containerWidths as $containerClass => $width) {
            if (strpos($class, $containerClass) !== false) {
                return $width;
            }
        }

        return null;
    }

    /**
     * Detect section vertical alignment
     *
     * @param \DOMElement $node Section/row element
     * @return string|null Alignment value or null
     */
    public static function detectVerticalAlign(\DOMElement $node): ?string
    {
        $style = $node->getAttribute('style');

        // Check align-items
        if (preg_match('/align-items\s*:\s*(flex-start|flex-end|center|stretch|baseline)/i', $style, $m)) {
            $alignMap = [
                'flex-start' => 'top',
                'flex-end' => 'bottom',
                'center' => 'center',
                'stretch' => 'stretch',
                'baseline' => 'baseline'
            ];
            return $alignMap[strtolower($m[1])] ?? null;
        }

        // Check explicit data attribute
        $explicit = $node->getAttribute('data-jtb-attr-vertical_align');
        if (!empty($explicit)) {
            return $explicit;
        }

        return null;
    }

    /**
     * Detect gap/gutter between columns
     *
     * @param \DOMElement $node Row element
     * @return int|null Gap in pixels
     */
    public static function detectColumnGap(\DOMElement $node): ?int
    {
        $style = $node->getAttribute('style');

        // Check explicit data attribute
        $explicit = $node->getAttribute('data-jtb-attr-column_gap');
        if (!empty($explicit)) {
            return (int)$explicit;
        }

        // Check gap property
        if (preg_match('/\bgap\s*:\s*(\d+)(?:px)?(?:\s+(\d+)(?:px)?)?/', $style, $m)) {
            // If two values, second is column gap; if one value, it's both
            return isset($m[2]) ? (int)$m[2] : (int)$m[1];
        }

        // Check column-gap
        if (preg_match('/column-gap\s*:\s*(\d+)(?:px)?/', $style, $m)) {
            return (int)$m[1];
        }

        // Check grid-column-gap
        if (preg_match('/grid-column-gap\s*:\s*(\d+)(?:px)?/', $style, $m)) {
            return (int)$m[1];
        }

        return null;
    }

    /**
     * Detect row gap
     *
     * @param \DOMElement $node Row element
     * @return int|null Gap in pixels
     */
    public static function detectRowGap(\DOMElement $node): ?int
    {
        $style = $node->getAttribute('style');

        // Check explicit data attribute
        $explicit = $node->getAttribute('data-jtb-attr-row_gap');
        if (!empty($explicit)) {
            return (int)$explicit;
        }

        // Check gap property
        if (preg_match('/\bgap\s*:\s*(\d+)(?:px)?/', $style, $m)) {
            return (int)$m[1];
        }

        // Check row-gap
        if (preg_match('/row-gap\s*:\s*(\d+)(?:px)?/', $style, $m)) {
            return (int)$m[1];
        }

        // Check grid-row-gap
        if (preg_match('/grid-row-gap\s*:\s*(\d+)(?:px)?/', $style, $m)) {
            return (int)$m[1];
        }

        return null;
    }
}
