<?php
declare(strict_types=1);
/**
 * Section Detector for HTML to TB Converter
 * 
 * Detects logical sections in HTML documents using semantic tags,
 * class names, IDs, and structural patterns.
 *
 * @package ThemeBuilder
 * @subpackage HtmlConverter
 * @version 4.0
 */

namespace Core\ThemeBuilder\HtmlConverter;

class SectionDetector
{
    /**
     * Semantic HTML5 tags that define sections
     */
    private array $semanticTags = [
        'header', 'footer', 'nav', 'main', 'article', 
        'section', 'aside', 'figure', 'figcaption'
    ];
    
    /**
     * Common class/ID patterns that indicate sections
     */
    private array $sectionPatterns = [
        'hero', 'banner', 'header', 'footer', 'navigation', 'nav',
        'features', 'services', 'about', 'about-us', 'testimonials',
        'team', 'portfolio', 'gallery', 'pricing', 'contact',
        'cta', 'call-to-action', 'faq', 'blog', 'news',
        'clients', 'partners', 'sponsors', 'stats', 'counter',
        'process', 'how-it-works', 'benefits', 'why-us',
        'subscribe', 'newsletter', 'social', 'intro', 'welcome'
    ];
    
    /**
     * Detect all sections in an HTML document
     *
     * @param \DOMDocument $doc HTML document
     * @return array Array of DOMElement sections
     */
    public function detect(\DOMDocument $doc): array
    {
        $sections = [];
        
        // First, try to find body element
        $body = $doc->getElementsByTagName('body')->item(0);
        if (!$body) {
            // No body - treat entire document as content
            $body = $doc->documentElement;
        }
        
        if (!$body) {
            return [];
        }
        
        // Strategy 1: Find semantic HTML5 section elements
        $sections = array_merge($sections, $this->findSemanticSections($body));
        
        // Strategy 2: Find sections by class/ID patterns
        $sections = array_merge($sections, $this->findPatternSections($body));
        
        // Strategy 3: Find div-based sections (common wrapper pattern)
        $sections = array_merge($sections, $this->findDivSections($body));
        
        // Remove duplicates and nested sections
        $sections = $this->deduplicateSections($sections);
        
        // If no sections found, treat body children as sections
        if (empty($sections)) {
            $sections = $this->createSectionsFromChildren($body);
        }
        
        // Sort sections by document order
        $sections = $this->sortByDocumentOrder($sections);
        
        return $sections;
    }

    /**
     * Find sections using semantic HTML5 tags
     */
    private function findSemanticSections(\DOMElement $root): array
    {
        $sections = [];
        
        foreach ($this->semanticTags as $tag) {
            $elements = $root->getElementsByTagName($tag);
            foreach ($elements as $element) {
                if ($this->isValidSection($element)) {
                    $sections[] = $element;
                }
            }
        }
        
        return $sections;
    }
    
    /**
     * Find sections by class/ID pattern matching
     */
    private function findPatternSections(\DOMElement $root): array
    {
        $sections = [];
        $xpath = new \DOMXPath($root->ownerDocument);
        
        foreach ($this->sectionPatterns as $pattern) {
            // Search by class
            $classQuery = ".//*[contains(concat(' ', normalize-space(@class), ' '), ' {$pattern} ') or " .
                         "contains(concat(' ', normalize-space(@class), ' '), ' {$pattern}-') or " .
                         "contains(concat(' ', normalize-space(@class), ' '), '-{$pattern} ') or " .
                         "contains(concat(' ', normalize-space(@class), ' '), '-{$pattern}-')]";
            
            $results = $xpath->query($classQuery, $root);
            foreach ($results as $element) {
                if ($this->isValidSection($element) && $this->isSectionLevel($element)) {
                    $sections[] = $element;
                }
            }
            
            // Search by ID
            $idQuery = ".//*[contains(@id, '{$pattern}')]";
            $results = $xpath->query($idQuery, $root);
            foreach ($results as $element) {
                if ($this->isValidSection($element) && $this->isSectionLevel($element)) {
                    $sections[] = $element;
                }
            }
        }
        
        return $sections;
    }
    
    /**
     * Find div-based sections (wrapper divs with significant content)
     */
    private function findDivSections(\DOMElement $root): array
    {
        $sections = [];
        
        // Look for direct children of body that are divs
        foreach ($root->childNodes as $child) {
            if ($child->nodeType !== XML_ELEMENT_NODE) {
                continue;
            }
            
            /** @var \DOMElement $child */
            $tag = strtolower($child->tagName);
            
            // Skip already semantic elements
            if (in_array($tag, $this->semanticTags)) {
                continue;
            }
            
            // Check if it's a wrapper div with content
            if ($tag === 'div' && $this->hasSignificantContent($child)) {
                $sections[] = $child;
            }
        }
        
        return $sections;
    }
    
    /**
     * Create sections from body's direct children
     */
    private function createSectionsFromChildren(\DOMElement $body): array
    {
        $sections = [];
        
        foreach ($body->childNodes as $child) {
            if ($child->nodeType !== XML_ELEMENT_NODE) {
                continue;
            }
            
            /** @var \DOMElement $child */
            if ($this->hasSignificantContent($child)) {
                $sections[] = $child;
            }
        }
        
        return $sections;
    }

    /**
     * Check if element is a valid section candidate
     */
    private function isValidSection(\DOMElement $element): bool
    {
        // Must have some content
        if (!$this->hasSignificantContent($element)) {
            return false;
        }
        
        // Skip script, style, and other non-content elements
        $skipTags = ['script', 'style', 'link', 'meta', 'noscript', 'template'];
        if (in_array(strtolower($element->tagName), $skipTags)) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Check if element is at section level (not deeply nested)
     */
    private function isSectionLevel(\DOMElement $element): bool
    {
        $depth = 0;
        $parent = $element->parentNode;
        
        while ($parent && $parent->nodeType === XML_ELEMENT_NODE) {
            /** @var \DOMElement $parent */
            $tag = strtolower($parent->tagName);
            
            // Stop at body or html
            if (in_array($tag, ['body', 'html'])) {
                break;
            }
            
            // If parent is semantic section, this might be nested
            if (in_array($tag, $this->semanticTags)) {
                return false;
            }
            
            $depth++;
            $parent = $parent->parentNode;
            
            // Too deeply nested
            if ($depth > 3) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Check if element has significant content
     */
    private function hasSignificantContent(\DOMElement $element): bool
    {
        // Check for text content
        $text = trim($element->textContent);
        if (strlen($text) > 10) {
            return true;
        }
        
        // Check for meaningful child elements
        $meaningfulTags = ['h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'p', 'img', 'video', 'form', 'ul', 'ol', 'table'];
        foreach ($meaningfulTags as $tag) {
            if ($element->getElementsByTagName($tag)->length > 0) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Remove duplicate and nested sections
     */
    private function deduplicateSections(array $sections): array
    {
        $unique = [];
        $seen = new \SplObjectStorage();
        
        foreach ($sections as $section) {
            // Skip if already seen
            if ($seen->contains($section)) {
                continue;
            }
            
            // Skip if this section is a child of another section
            $isNested = false;
            foreach ($unique as $existingSection) {
                if ($this->isDescendantOf($section, $existingSection)) {
                    $isNested = true;
                    break;
                }
            }
            
            if (!$isNested) {
                // Remove any existing sections that are children of this one
                $unique = array_filter($unique, function($existing) use ($section) {
                    return !$this->isDescendantOf($existing, $section);
                });
                
                $unique[] = $section;
                $seen->attach($section);
            }
        }
        
        return array_values($unique);
    }

    /**
     * Check if element is a descendant of another element
     */
    private function isDescendantOf(\DOMElement $element, \DOMElement $ancestor): bool
    {
        $parent = $element->parentNode;
        
        while ($parent) {
            if ($parent === $ancestor) {
                return true;
            }
            $parent = $parent->parentNode;
        }
        
        return false;
    }
    
    /**
     * Sort sections by their position in the document
     */
    private function sortByDocumentOrder(array $sections): array
    {
        usort($sections, function(\DOMElement $a, \DOMElement $b) {
            // Get document position using XPath
            $xpath = new \DOMXPath($a->ownerDocument);
            
            $posA = $this->getDocumentPosition($a, $xpath);
            $posB = $this->getDocumentPosition($b, $xpath);
            
            return $posA - $posB;
        });
        
        return $sections;
    }
    
    /**
     * Get approximate document position of an element
     */
    private function getDocumentPosition(\DOMElement $element, \DOMXPath $xpath): int
    {
        // Count preceding elements
        $query = 'count(preceding::*) + count(ancestor::*)';
        return (int) $xpath->evaluate($query, $element);
    }
    
    /**
     * Get section type hint from element
     */
    public function getSectionType(\DOMElement $element): string
    {
        $tag = strtolower($element->tagName);
        $class = strtolower($element->getAttribute('class'));
        $id = strtolower($element->getAttribute('id'));
        
        // Check tag first
        if ($tag === 'header') return 'header';
        if ($tag === 'footer') return 'footer';
        if ($tag === 'nav') return 'navigation';
        if ($tag === 'aside') return 'sidebar';
        
        // Check patterns in class/ID
        foreach ($this->sectionPatterns as $pattern) {
            if (strpos($class, $pattern) !== false || strpos($id, $pattern) !== false) {
                return $pattern;
            }
        }
        
        return 'section';
    }
    
    /**
     * Detect if section appears to be full-width/hero style
     */
    public function isFullWidthSection(\DOMElement $element): bool
    {
        $class = strtolower($element->getAttribute('class'));
        $style = $element->getAttribute('style');
        
        // Check common full-width class patterns
        $fullWidthPatterns = ['full-width', 'fullwidth', 'full-screen', 'fullscreen', 
                             'hero', 'banner', 'cover', 'jumbotron'];
        
        foreach ($fullWidthPatterns as $pattern) {
            if (strpos($class, $pattern) !== false) {
                return true;
            }
        }
        
        // Check inline style for width: 100vw or similar
        if (preg_match('/width\s*:\s*100(vw|%)/i', $style)) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Detect section background type
     */
    public function detectBackgroundType(\DOMElement $element): string
    {
        $style = $element->getAttribute('style');
        $class = strtolower($element->getAttribute('class'));
        
        // Check for gradient
        if (preg_match('/gradient/i', $style) || strpos($class, 'gradient') !== false) {
            return 'gradient';
        }
        
        // Check for background image
        if (preg_match('/background(-image)?\s*:\s*url/i', $style)) {
            return 'image';
        }
        
        // Check for video background indicators
        if (strpos($class, 'video-bg') !== false || strpos($class, 'video-background') !== false) {
            return 'video';
        }
        
        // Check for background color
        if (preg_match('/background(-color)?\s*:\s*[^;]+/i', $style)) {
            return 'color';
        }
        
        return 'none';
    }
}
