<?php
declare(strict_types=1);
/**
 * HTML to Theme Builder JSON Converter
 * 
 * Main orchestrator class that converts free-form HTML/CSS
 * into TB 3.0 compatible JSON structure.
 *
 * @package ThemeBuilder
 * @subpackage HtmlConverter
 * @version 4.0.2
 */

namespace Core\ThemeBuilder\HtmlConverter;

class Converter
{
    private ElementMapper $mapper;
    private StyleExtractor $styleExtractor;
    private SectionDetector $sectionDetector;
    private LayoutAnalyzer $layoutAnalyzer;
    
    private array $globalStyles = [];
    private int $idCounter = 0;
    private array $processedGridIds = []; // Track processed grids to avoid duplicates
    
    public function __construct()
    {
        require_once __DIR__ . '/ElementMapper.php';
        require_once __DIR__ . '/StyleExtractor.php';
        require_once __DIR__ . '/SectionDetector.php';
        require_once __DIR__ . '/LayoutAnalyzer.php';
        
        $this->mapper = new ElementMapper();
        $this->styleExtractor = new StyleExtractor();
        $this->sectionDetector = new SectionDetector();
        $this->layoutAnalyzer = new LayoutAnalyzer();
    }
    
    /**
     * Convert HTML string to TB JSON structure
     */
    public function convert(string $html): array
    {
        $this->idCounter = 0;
        $this->globalStyles = [];
        $this->processedGridIds = [];
        
        $doc = new \DOMDocument();
        $doc->encoding = 'UTF-8';
        
        libxml_use_internal_errors(true);
        
        if (stripos($html, '<html') === false) {
            $html = '<!DOCTYPE html><html><head><meta charset="UTF-8"></head><body>' . $html . '</body></html>';
        }
        
        $doc->loadHTML('<?xml encoding="UTF-8">' . $html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NOERROR);
        libxml_clear_errors();
        
        $this->globalStyles = $this->styleExtractor->extractGlobalStyles($doc);
        $sections = $this->sectionDetector->detect($doc);
        
        $tbSections = [];
        foreach ($sections as $section) {
            $converted = $this->convertSection($section);
            if ($converted) {
                $tbSections[] = $converted;
            }
        }
        
        return ['sections' => $tbSections];
    }
    
    /**
     * Convert a single HTML section to TB section structure
     */
    private function convertSection(\DOMElement $section): ?array
    {
        $sectionName = $this->detectSectionName($section);
        $sectionDesign = $this->styleExtractor->extractDesign($section, $this->globalStyles);
        $this->processedGridIds = []; // Reset per section
        
        $rows = $this->buildRows($section);
        
        if (empty($rows)) {
            return null;
        }
        
        return [
            'id' => $this->generateId('section'),
            'name' => $sectionName,
            'design' => $sectionDesign,
            'rows' => $rows
        ];
    }
    
    /**
     * Build TB rows from section content
     */
    private function buildRows(\DOMElement $section): array
    {
        $rows = [];
        $currentModules = [];
        
        // Process all children, detecting grids along the way
        $this->processElement($section, $rows, $currentModules, 0);
        
        // Flush remaining modules
        if (!empty($currentModules)) {
            $rows[] = $this->createSingleColumnRow($currentModules);
        }
        
        return $rows;
    }
    
    /**
     * Process element recursively, building rows and detecting grids
     */
    private function processElement(\DOMElement $element, array &$rows, array &$currentModules, int $depth): void
    {
        foreach ($element->childNodes as $child) {
            if ($child->nodeType !== XML_ELEMENT_NODE) {
                continue;
            }
            
            /** @var \DOMElement $child */
            $childId = spl_object_id($child);
            
            // Skip if already processed as grid
            if (in_array($childId, $this->processedGridIds)) {
                continue;
            }
            
            // Check if this element is a grid/flex container
            $layoutInfo = $this->layoutAnalyzer->analyze($child);
            
            if (($layoutInfo['type'] === 'grid' || $layoutInfo['type'] === 'flex') && count($layoutInfo['columns']) > 1) {
                // Mark as processed
                $this->processedGridIds[] = $childId;
                foreach ($layoutInfo['columns'] as $col) {
                    $this->processedGridIds[] = spl_object_id($col['element']);
                }
                
                // Flush current modules before grid
                if (!empty($currentModules)) {
                    $rows[] = $this->createSingleColumnRow($currentModules);
                    $currentModules = [];
                }
                
                // Add multi-column row
                $gridRow = $this->buildGridRow($child, $layoutInfo);
                if ($gridRow) {
                    $rows[] = $gridRow;
                }
            } else {
                // Try to map element to module
                $module = $this->mapper->mapElement($child, $this->globalStyles, $this->styleExtractor);
                
                if ($module) {
                    $module['id'] = $this->generateId('mod');
                    $currentModules[] = $module;
                } else {
                    // Not a module - recurse into children (max depth 4)
                    if ($depth < 4) {
                        $this->processElement($child, $rows, $currentModules, $depth + 1);
                    }
                }
            }
        }
    }
    
    /**
     * Create a single-column row
     */
    private function createSingleColumnRow(array $modules): array
    {
        return [
            'id' => $this->generateId('row'),
            'columns' => [[
                'id' => $this->generateId('col'),
                'width' => '100%',
                'modules' => $modules
            ]]
        ];
    }
    
    /**
     * Build a multi-column row from grid/flex container
     */
    private function buildGridRow(\DOMElement $gridElement, array $layoutInfo): ?array
    {
        $columns = [];
        
        foreach ($layoutInfo['columns'] as $colInfo) {
            $colElement = $colInfo['element'];
            $modules = [];
            
            // First try to map the column element itself
            $module = $this->mapper->mapElement($colElement, $this->globalStyles, $this->styleExtractor);
            
            if ($module) {
                $module['id'] = $this->generateId('mod');
                $modules[] = $module;
            } else {
                // Convert children
                $modules = $this->convertChildrenToModules($colElement);
            }
            
            if (!empty($modules)) {
                $columns[] = [
                    'id' => $this->generateId('col'),
                    'width' => $colInfo['width'],
                    'modules' => $modules
                ];
            }
        }
        
        if (empty($columns)) {
            return null;
        }
        
        return [
            'id' => $this->generateId('row'),
            'columns' => $columns
        ];
    }
    
    /**
     * Convert child elements to TB modules
     */
    private function convertChildrenToModules(\DOMElement $parent): array
    {
        $modules = [];
        
        foreach ($parent->childNodes as $child) {
            if ($child->nodeType !== XML_ELEMENT_NODE) {
                continue;
            }
            
            /** @var \DOMElement $child */
            $module = $this->mapper->mapElement($child, $this->globalStyles, $this->styleExtractor);
            
            if ($module) {
                $module['id'] = $this->generateId('mod');
                $modules[] = $module;
            } else {
                $childModules = $this->convertChildrenToModules($child);
                $modules = array_merge($modules, $childModules);
            }
        }
        
        return $modules;
    }
    
    /**
     * Detect section name from element attributes
     */
    private function detectSectionName(\DOMElement $section): string
    {
        $id = $section->getAttribute('id');
        if ($id) {
            return ucfirst(str_replace(['-', '_'], ' ', $id));
        }
        
        $class = $section->getAttribute('class');
        $sectionKeywords = [
            'hero' => 'Hero', 'header' => 'Header', 'banner' => 'Banner',
            'features' => 'Features', 'services' => 'Services', 'about' => 'About',
            'testimonials' => 'Testimonials', 'team' => 'Team', 'portfolio' => 'Portfolio',
            'gallery' => 'Gallery', 'pricing' => 'Pricing', 'contact' => 'Contact',
            'cta' => 'Call to Action', 'footer' => 'Footer', 'faq' => 'FAQ',
            'stats' => 'Statistics', 'clients' => 'Clients', 'partners' => 'Partners'
        ];
        
        foreach ($sectionKeywords as $keyword => $name) {
            if (stripos($class, $keyword) !== false) {
                return $name;
            }
        }
        
        $tag = strtolower($section->tagName);
        if ($tag === 'header') return 'Header';
        if ($tag === 'footer') return 'Footer';
        if ($tag === 'nav') return 'Navigation';
        if ($tag === 'article') return 'Article';
        
        return 'Section';
    }
    
    /**
     * Generate unique ID
     */
    private function generateId(string $prefix): string
    {
        $this->idCounter++;
        return $prefix . '_' . $this->idCounter . '_' . substr(uniqid(), -4);
    }
}
