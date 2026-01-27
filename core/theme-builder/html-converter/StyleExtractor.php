<?php
declare(strict_types=1);
/**
 * CSS Style Extractor for HTML to TB Converter
 * 
 * Extracts CSS styles from HTML elements and converts them
 * to Theme Builder design properties.
 *
 * @package ThemeBuilder
 * @subpackage HtmlConverter
 * @version 4.0
 */

namespace Core\ThemeBuilder\HtmlConverter;

class StyleExtractor
{
    /**
     * CSS property to TB design property mapping
     */
    private array $cssToDesignMap = [
        // Typography
        'font-size' => 'font_size',
        'font-weight' => 'font_weight',
        'font-family' => 'font_family',
        'line-height' => 'line_height',
        'letter-spacing' => 'letter_spacing',
        'text-align' => 'text_align',
        'text-transform' => 'text_transform',
        'color' => 'text_color',
        
        // Spacing
        'padding' => 'padding',
        'padding-top' => 'padding_top',
        'padding-right' => 'padding_right',
        'padding-bottom' => 'padding_bottom',
        'padding-left' => 'padding_left',
        'margin' => 'margin',
        'margin-top' => 'margin_top',
        'margin-right' => 'margin_right',
        'margin-bottom' => 'margin_bottom',
        'margin-left' => 'margin_left',
        
        // Background
        'background-color' => 'background_color',
        'background-image' => 'background_image',
        'background-size' => 'background_size',
        'background-position' => 'background_position',
        'background-repeat' => 'background_repeat',
        
        // Border
        'border-radius' => 'border_radius',
        'border-width' => 'border_width',
        'border-style' => 'border_style',
        'border-color' => 'border_color',
        
        // Effects
        'box-shadow' => 'box_shadow',
        'opacity' => 'opacity',
        
        // Dimensions
        'width' => 'width',
        'max-width' => 'max_width',
        'min-width' => 'min_width',
        'height' => 'height',
        'max-height' => 'max_height',
        'min-height' => 'min_height',
    ];
    
    private array $parsedStyles = [];
    private array $cssVariables = [];
    
    public function extractGlobalStyles(\DOMDocument $doc): array
    {
        $this->parsedStyles = [];
        $this->cssVariables = [];
        
        $styleTags = $doc->getElementsByTagName('style');
        
        foreach ($styleTags as $styleTag) {
            $css = $styleTag->textContent;
            $this->parseCSS($css);
        }
        
        return $this->parsedStyles;
    }
    
    public function extractDesign(\DOMElement $element, array $globalStyles): array
    {
        $design = [];
        $design = array_merge($design, $this->getStylesFromGlobal($element, $globalStyles));
        $inlineStyles = $this->parseInlineStyle($element->getAttribute('style'));
        $design = array_merge($design, $inlineStyles);
        $design = $this->normalizeDesign($design);
        
        return $design;
    }
    
    private function parseCSS(string $css): void
    {
        $css = preg_replace('/\/\*.*?\*\//s', '', $css);
        
        if (preg_match('/:root\s*\{([^}]+)\}/s', $css, $rootMatch)) {
            $this->parseCSSVariables($rootMatch[1]);
        }
        
        preg_match_all('/([^{]+)\{([^}]+)\}/s', $css, $matches, PREG_SET_ORDER);
        
        foreach ($matches as $match) {
            $selectors = array_map('trim', explode(',', $match[1]));
            $properties = $this->parseProperties($match[2]);
            
            foreach ($selectors as $selector) {
                $selector = trim($selector);
                if (empty($selector) || $selector === ':root') {
                    continue;
                }
                
                if (!isset($this->parsedStyles[$selector])) {
                    $this->parsedStyles[$selector] = [];
                }
                
                $this->parsedStyles[$selector] = array_merge(
                    $this->parsedStyles[$selector],
                    $properties
                );
            }
        }
    }
    
    private function parseCSSVariables(string $content): void
    {
        preg_match_all('/(--[a-zA-Z0-9-]+)\s*:\s*([^;]+);/', $content, $matches, PREG_SET_ORDER);
        
        foreach ($matches as $match) {
            $this->cssVariables[trim($match[1])] = trim($match[2]);
        }
    }

    private function parseProperties(string $content): array
    {
        $properties = [];
        $declarations = preg_split('/;(?![^(]*\))/', $content);
        
        foreach ($declarations as $declaration) {
            $declaration = trim($declaration);
            if (empty($declaration)) {
                continue;
            }
            
            $colonPos = strpos($declaration, ':');
            if ($colonPos === false) {
                continue;
            }
            
            $property = trim(substr($declaration, 0, $colonPos));
            $value = trim(substr($declaration, $colonPos + 1));
            
            if (str_starts_with($property, '--')) {
                continue;
            }
            
            $value = $this->resolveCSSVariables($value);
            
            if (isset($this->cssToDesignMap[$property])) {
                $tbProperty = $this->cssToDesignMap[$property];
                $properties[$tbProperty] = $value;
            }
            
            $this->expandShorthand($property, $value, $properties);
        }
        
        return $properties;
    }
    
    private function parseInlineStyle(string $style): array
    {
        if (empty($style)) {
            return [];
        }
        return $this->parseProperties($style);
    }
    
    private function getStylesFromGlobal(\DOMElement $element, array $globalStyles): array
    {
        $design = [];
        
        $id = $element->getAttribute('id');
        $classes = array_filter(explode(' ', $element->getAttribute('class')));
        $tag = strtolower($element->tagName);
        
        if (isset($globalStyles[$tag])) {
            $design = array_merge($design, $globalStyles[$tag]);
        }
        
        foreach ($classes as $class) {
            $class = trim($class);
            if (empty($class)) {
                continue;
            }
            
            $classSelector = '.' . $class;
            if (isset($globalStyles[$classSelector])) {
                $design = array_merge($design, $globalStyles[$classSelector]);
            }
            
            $tagClassSelector = $tag . $classSelector;
            if (isset($globalStyles[$tagClassSelector])) {
                $design = array_merge($design, $globalStyles[$tagClassSelector]);
            }
        }
        
        if ($id) {
            $idSelector = '#' . $id;
            if (isset($globalStyles[$idSelector])) {
                $design = array_merge($design, $globalStyles[$idSelector]);
            }
        }
        
        return $design;
    }
    
    private function resolveCSSVariables(string $value): string
    {
        return preg_replace_callback(
            '/var\(\s*(--[a-zA-Z0-9-]+)\s*(?:,\s*([^)]+))?\s*\)/',
            function ($matches) {
                $varName = $matches[1];
                $fallback = $matches[2] ?? '';
                return $this->cssVariables[$varName] ?? trim($fallback) ?: $matches[0];
            },
            $value
        );
    }

    private function expandShorthand(string $property, string $value, array &$properties): void
    {
        switch ($property) {
            case 'padding':
                $this->expandBoxModel($value, 'padding', $properties);
                break;
            case 'margin':
                $this->expandBoxModel($value, 'margin', $properties);
                break;
            case 'border':
                $this->expandBorder($value, $properties);
                break;
            case 'background':
                $this->expandBackground($value, $properties);
                break;
        }
    }
    
    private function expandBoxModel(string $value, string $prefix, array &$properties): void
    {
        $parts = preg_split('/\s+/', trim($value));
        $count = count($parts);
        
        switch ($count) {
            case 1:
                $properties[$prefix . '_top'] = $parts[0];
                $properties[$prefix . '_right'] = $parts[0];
                $properties[$prefix . '_bottom'] = $parts[0];
                $properties[$prefix . '_left'] = $parts[0];
                break;
            case 2:
                $properties[$prefix . '_top'] = $parts[0];
                $properties[$prefix . '_bottom'] = $parts[0];
                $properties[$prefix . '_right'] = $parts[1];
                $properties[$prefix . '_left'] = $parts[1];
                break;
            case 3:
                $properties[$prefix . '_top'] = $parts[0];
                $properties[$prefix . '_right'] = $parts[1];
                $properties[$prefix . '_left'] = $parts[1];
                $properties[$prefix . '_bottom'] = $parts[2];
                break;
            case 4:
                $properties[$prefix . '_top'] = $parts[0];
                $properties[$prefix . '_right'] = $parts[1];
                $properties[$prefix . '_bottom'] = $parts[2];
                $properties[$prefix . '_left'] = $parts[3];
                break;
        }
    }
    
    private function expandBorder(string $value, array &$properties): void
    {
        $parts = preg_split('/\s+/', trim($value));
        
        foreach ($parts as $part) {
            if (preg_match('/^\d+(\.\d+)?(px|em|rem|%)?$/', $part)) {
                $properties['border_width'] = $part;
            } elseif (in_array($part, ['solid', 'dashed', 'dotted', 'double', 'none', 'hidden'])) {
                $properties['border_style'] = $part;
            } elseif (preg_match('/^(#|rgb|hsl|[a-z]+)/i', $part)) {
                $properties['border_color'] = $part;
            }
        }
    }

    private function expandBackground(string $value, array &$properties): void
    {
        if (preg_match('/url\(["\']?([^"\'()]+)["\']?\)/', $value, $urlMatch)) {
            $properties['background_image'] = $urlMatch[1];
        }
        
        if (preg_match('/(#[0-9a-fA-F]{3,8}|rgb\([^)]+\)|rgba\([^)]+\)|hsl\([^)]+\)|hsla\([^)]+\)|\b[a-z]+\b)(?!\()/', $value, $colorMatch)) {
            $color = $colorMatch[1];
            if (!in_array($color, ['url', 'no-repeat', 'repeat', 'cover', 'contain', 'center', 'top', 'bottom', 'left', 'right'])) {
                $properties['background_color'] = $color;
            }
        }
        
        if (preg_match('/\b(cover|contain)\b/', $value, $sizeMatch)) {
            $properties['background_size'] = $sizeMatch[1];
        }
        
        if (preg_match('/\b(no-repeat|repeat-x|repeat-y|repeat)\b/', $value, $repeatMatch)) {
            $properties['background_repeat'] = $repeatMatch[1];
        }
        
        if (preg_match('/\b(center|top|bottom|left|right)(\s+(center|top|bottom|left|right))?\b/', $value, $posMatch)) {
            $properties['background_position'] = trim($posMatch[0]);
        }
    }
    
    private function normalizeDesign(array $design): array
    {
        $normalized = [];
        
        foreach ($design as $key => $value) {
            if ($value === '' || $value === null) {
                continue;
            }
            
            $value = trim($value);
            $value = str_replace('!important', '', $value);
            $value = trim($value);
            
            if (in_array($key, ['text_color', 'background_color', 'border_color'])) {
                $value = $this->normalizeColor($value);
            }
            
            $normalized[$key] = $value;
        }
        
        return $normalized;
    }
    
    private function normalizeColor(string $color): string
    {
        if (preg_match('/^#([0-9a-fA-F])([0-9a-fA-F])([0-9a-fA-F])$/', $color, $match)) {
            return '#' . $match[1] . $match[1] . $match[2] . $match[2] . $match[3] . $match[3];
        }
        return $color;
    }
    
    public function extractGradient(string $value): ?string
    {
        if (preg_match('/(linear-gradient|radial-gradient|conic-gradient)\([^)]+\)/', $value, $match)) {
            return $match[0];
        }
        return null;
    }
}
