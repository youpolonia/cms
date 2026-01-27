<?php
declare(strict_types=1);
/**
 * Layout Analyzer for HTML to TB Converter
 * 
 * Analyzes CSS layouts (grid, flexbox) and detects column structures
 * in HTML elements for conversion to TB row/column format.
 *
 * @package ThemeBuilder
 * @subpackage HtmlConverter
 * @version 4.0
 */

namespace Core\ThemeBuilder\HtmlConverter;

class LayoutAnalyzer
{
    /**
     * Common CSS framework column class patterns
     */
    private array $columnPatterns = [
        // Bootstrap
        'col-' => 'bootstrap',
        'col-sm-' => 'bootstrap',
        'col-md-' => 'bootstrap',
        'col-lg-' => 'bootstrap',
        'col-xl-' => 'bootstrap',
        // Foundation
        'small-' => 'foundation',
        'medium-' => 'foundation',
        'large-' => 'foundation',
        // Tailwind
        'w-1/' => 'tailwind',
        'md:w-' => 'tailwind',
        'lg:w-' => 'tailwind',
        // Bulma
        'column is-' => 'bulma',
        // Generic
        'col' => 'generic',
        'column' => 'generic',
    ];
    
    /**
     * Grid column count keywords
     */
    private array $gridColumnKeywords = [
        'one' => 1, 'two' => 2, 'three' => 3, 'four' => 4,
        'five' => 5, 'six' => 6, 'seven' => 7, 'eight' => 8,
        'nine' => 9, 'ten' => 10, 'eleven' => 11, 'twelve' => 12
    ];

    /**
     * Analyze layout of a section element
     *
     * @param \DOMElement $section Section to analyze
     * @return array Layout info with type and columns
     */
    public function analyze(\DOMElement $section): array
    {
        // Check inline styles first
        $style = $section->getAttribute('style');
        
        // Check for CSS Grid
        if ($this->isGridLayout($section, $style)) {
            return $this->analyzeGridLayout($section);
        }
        
        // Check for Flexbox
        if ($this->isFlexLayout($section, $style)) {
            return $this->analyzeFlexLayout($section);
        }
        
        // Check for framework columns (Bootstrap, etc.)
        $frameworkColumns = $this->detectFrameworkColumns($section);
        if (!empty($frameworkColumns)) {
            return [
                'type' => 'flex',
                'columns' => $frameworkColumns
            ];
        }
        
        // Check for structural columns (multiple similar children)
        $structuralColumns = $this->detectStructuralColumns($section);
        if (!empty($structuralColumns)) {
            return [
                'type' => 'flex',
                'columns' => $structuralColumns
            ];
        }
        
        // Default: single column
        return [
            'type' => 'single',
            'columns' => []
        ];
    }

    /**
     * Check if element uses CSS Grid layout
     */
    private function isGridLayout(\DOMElement $element, string $style): bool
    {
        // Check inline style
        if (preg_match('/display\s*:\s*grid/i', $style)) {
            return true;
        }
        
        // Check common grid classes
        $class = $element->getAttribute('class');
        $gridClasses = ['grid', 'css-grid', 'd-grid', 'grid-container'];
        
        foreach ($gridClasses as $gridClass) {
            if (preg_match('/\b' . preg_quote($gridClass, '/') . '\b/i', $class)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Check if element uses Flexbox layout
     */
    private function isFlexLayout(\DOMElement $element, string $style): bool
    {
        // Check inline style
        if (preg_match('/display\s*:\s*flex/i', $style)) {
            return true;
        }
        
        // Check common flex classes
        $class = $element->getAttribute('class');
        $flexClasses = ['flex', 'd-flex', 'flexbox', 'flex-container', 'row', 'columns'];
        
        foreach ($flexClasses as $flexClass) {
            if (preg_match('/\b' . preg_quote($flexClass, '/') . '\b/i', $class)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Analyze CSS Grid layout
     */
    private function analyzeGridLayout(\DOMElement $element): array
    {
        $columns = [];
        $style = $element->getAttribute('style');
        
        // Try to detect grid-template-columns
        $columnCount = $this->detectGridColumnCount($style, $element);
        
        // Get direct children as columns
        $children = $this->getElementChildren($element);
        
        if (empty($children)) {
            return ['type' => 'single', 'columns' => []];
        }
        
        // Calculate width based on detected columns or child count
        $numColumns = $columnCount ?: count($children);
        $width = $this->calculateColumnWidth($numColumns);
        
        foreach ($children as $child) {
            $columns[] = [
                'element' => $child,
                'width' => $width
            ];
        }
        
        return [
            'type' => 'grid',
            'columns' => $columns
        ];
    }
    
    /**
     * Analyze Flexbox layout
     */
    private function analyzeFlexLayout(\DOMElement $element): array
    {
        $columns = [];
        $children = $this->getElementChildren($element);
        
        if (empty($children)) {
            return ['type' => 'single', 'columns' => []];
        }
        
        // Check if children have explicit widths
        $hasExplicitWidths = false;
        foreach ($children as $child) {
            if ($this->getExplicitWidth($child)) {
                $hasExplicitWidths = true;
                break;
            }
        }
        
        if ($hasExplicitWidths) {
            // Use explicit widths
            foreach ($children as $child) {
                $width = $this->getExplicitWidth($child) ?: $this->calculateColumnWidth(count($children));
                $columns[] = [
                    'element' => $child,
                    'width' => $width
                ];
            }
        } else {
            // Equal distribution
            $width = $this->calculateColumnWidth(count($children));
            foreach ($children as $child) {
                $columns[] = [
                    'element' => $child,
                    'width' => $width
                ];
            }
        }
        
        return [
            'type' => 'flex',
            'columns' => $columns
        ];
    }

    /**
     * Detect columns from CSS framework classes
     */
    private function detectFrameworkColumns(\DOMElement $element): array
    {
        $columns = [];
        $children = $this->getElementChildren($element);
        
        if (empty($children)) {
            return [];
        }
        
        $hasColumnClasses = false;
        
        foreach ($children as $child) {
            $class = $child->getAttribute('class');
            $width = null;
            
            // Check Bootstrap columns
            if (preg_match('/col(-[a-z]{2})?-(\d{1,2})/i', $class, $match)) {
                $width = ($match[2] / 12 * 100) . '%';
                $hasColumnClasses = true;
            }
            // Check Tailwind width classes
            elseif (preg_match('/w-(\d+)\/(\d+)/', $class, $match)) {
                $width = ($match[1] / $match[2] * 100) . '%';
                $hasColumnClasses = true;
            }
            // Check Foundation columns
            elseif (preg_match('/(small|medium|large)-(\d{1,2})/i', $class, $match)) {
                $width = ($match[2] / 12 * 100) . '%';
                $hasColumnClasses = true;
            }
            // Check Bulma columns
            elseif (preg_match('/is-(\d{1,2})/i', $class, $match)) {
                $width = ($match[1] / 12 * 100) . '%';
                $hasColumnClasses = true;
            }
            // Check generic column keywords
            elseif (preg_match('/\b(one|two|three|four|five|six|seven|eight|nine|ten|eleven|twelve)\b/i', $class, $match)) {
                $num = $this->gridColumnKeywords[strtolower($match[1])] ?? 0;
                if ($num > 0) {
                    $width = ($num / 12 * 100) . '%';
                    $hasColumnClasses = true;
                }
            }
            // Check generic col/column class
            elseif (preg_match('/\b(col|column)\b/i', $class)) {
                $hasColumnClasses = true;
            }
            
            if ($hasColumnClasses) {
                $columns[] = [
                    'element' => $child,
                    'width' => $width ?: $this->calculateColumnWidth(count($children))
                ];
            }
        }
        
        return $hasColumnClasses ? $columns : [];
    }
    
    /**
     * Detect structural columns (similar sibling elements)
     */
    private function detectStructuralColumns(\DOMElement $element): array
    {
        $children = $this->getElementChildren($element);
        
        if (count($children) < 2 || count($children) > 6) {
            return [];
        }
        
        // Check if children are structurally similar
        $firstTag = strtolower($children[0]->tagName);
        $similarCount = 0;
        
        foreach ($children as $child) {
            if (strtolower($child->tagName) === $firstTag) {
                $similarCount++;
            }
        }
        
        // At least 80% should be the same tag
        if ($similarCount / count($children) < 0.8) {
            return [];
        }
        
        // Check if children look like column content (have headers, text, etc.)
        $looksLikeColumns = true;
        foreach ($children as $child) {
            if (!$this->hasColumnLikeContent($child)) {
                $looksLikeColumns = false;
                break;
            }
        }
        
        if (!$looksLikeColumns) {
            return [];
        }
        
        // Build column array
        $columns = [];
        $width = $this->calculateColumnWidth(count($children));
        
        foreach ($children as $child) {
            $columns[] = [
                'element' => $child,
                'width' => $width
            ];
        }
        
        return $columns;
    }

    /**
     * Check if element has column-like content
     */
    private function hasColumnLikeContent(\DOMElement $element): bool
    {
        // Should have some meaningful content
        $text = trim($element->textContent);
        if (strlen($text) < 5) {
            return false;
        }
        
        // Bonus: has heading or image
        $hasHeading = $element->getElementsByTagName('h1')->length > 0 ||
                      $element->getElementsByTagName('h2')->length > 0 ||
                      $element->getElementsByTagName('h3')->length > 0 ||
                      $element->getElementsByTagName('h4')->length > 0;
        
        $hasImage = $element->getElementsByTagName('img')->length > 0;
        $hasIcon = strpos($element->getAttribute('class'), 'icon') !== false ||
                   $element->getElementsByTagName('i')->length > 0 ||
                   $element->getElementsByTagName('svg')->length > 0;
        
        return $hasHeading || $hasImage || $hasIcon || strlen($text) > 20;
    }
    
    /**
     * Get direct element children (skip text nodes)
     */
    private function getElementChildren(\DOMElement $element): array
    {
        $children = [];
        
        foreach ($element->childNodes as $child) {
            if ($child->nodeType === XML_ELEMENT_NODE) {
                /** @var \DOMElement $child */
                // Skip wrapper divs that only contain one element
                if (strtolower($child->tagName) === 'div') {
                    $innerChildren = $this->getElementChildren($child);
                    if (count($innerChildren) === 1 && $this->isWrapperDiv($child)) {
                        $children[] = $innerChildren[0];
                        continue;
                    }
                }
                $children[] = $child;
            }
        }
        
        return $children;
    }
    
    /**
     * Check if div is just a wrapper
     */
    private function isWrapperDiv(\DOMElement $element): bool
    {
        $class = $element->getAttribute('class');
        $wrapperPatterns = ['container', 'wrapper', 'inner', 'content'];
        
        foreach ($wrapperPatterns as $pattern) {
            if (stripos($class, $pattern) !== false) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Detect grid column count from style
     */
    private function detectGridColumnCount(string $style, \DOMElement $element): int
    {
        // Check grid-template-columns
        if (preg_match('/grid-template-columns\s*:\s*([^;]+)/i', $style, $match)) {
            $value = trim($match[1]);
            
            // repeat(N, ...)
            if (preg_match('/repeat\s*\(\s*(\d+)/', $value, $repeatMatch)) {
                return (int) $repeatMatch[1];
            }
            
            // Count explicit columns (1fr 1fr 1fr)
            $parts = preg_split('/\s+/', $value);
            $count = 0;
            foreach ($parts as $part) {
                if (preg_match('/\d+(fr|px|%|em|rem)|auto|min-content|max-content/', $part)) {
                    $count++;
                }
            }
            if ($count > 0) {
                return $count;
            }
        }
        
        // Check class for grid column hints
        $class = $element->getAttribute('class');
        if (preg_match('/grid-cols-(\d+)/i', $class, $match)) {
            return (int) $match[1];
        }
        
        return 0;
    }
    
    /**
     * Get explicit width from element
     */
    private function getExplicitWidth(\DOMElement $element): ?string
    {
        $style = $element->getAttribute('style');
        $class = $element->getAttribute('class');
        
        // Check inline width
        if (preg_match('/width\s*:\s*(\d+(?:\.\d+)?(%|px|vw|em|rem))/i', $style, $match)) {
            return $match[1];
        }
        
        // Check flex-basis
        if (preg_match('/flex-basis\s*:\s*(\d+(?:\.\d+)?(%|px))/i', $style, $match)) {
            return $match[1];
        }
        
        // Check Bootstrap column class
        if (preg_match('/col(-[a-z]{2})?-(\d{1,2})\b/i', $class, $match)) {
            return round($match[2] / 12 * 100, 2) . '%';
        }
        
        // Check Tailwind width
        if (preg_match('/w-(\d+)\/(\d+)/', $class, $match)) {
            return round($match[1] / $match[2] * 100, 2) . '%';
        }
        
        return null;
    }
    
    /**
     * Calculate column width for N columns
     */
    private function calculateColumnWidth(int $numColumns): string
    {
        if ($numColumns <= 0) {
            return '100%';
        }
        
        $percentage = round(100 / $numColumns, 2);
        return $percentage . '%';
    }
    
    /**
     * Detect if layout is responsive
     */
    public function isResponsiveLayout(\DOMElement $element): bool
    {
        $class = $element->getAttribute('class');
        
        // Check for responsive framework classes
        $responsivePatterns = [
            'col-sm-', 'col-md-', 'col-lg-', 'col-xl-',
            'sm:', 'md:', 'lg:', 'xl:',
            'small-', 'medium-', 'large-',
            'responsive', 'mobile-'
        ];
        
        foreach ($responsivePatterns as $pattern) {
            if (strpos($class, $pattern) !== false) {
                return true;
            }
        }
        
        return false;
    }
}
