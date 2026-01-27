<?php
/**
 * JTB HTML Parser - Main Orchestrator
 * Converts HTML to JTB content structure
 *
 * @package JessieThemeBuilder
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

// Load parser components
require_once __DIR__ . '/class-jtb-style-extractor.php';
require_once __DIR__ . '/class-jtb-attribute-converter.php';
require_once __DIR__ . '/class-jtb-layout-detector.php';
require_once __DIR__ . '/class-jtb-module-mapper.php';
require_once __DIR__ . '/class-jtb-component-recognizer.php';

class JTB_HTML_Parser
{
    /**
     * Parse mode constants
     */
    const MODE_JTB_ANNOTATED = 'jtb_annotated'; // HTML with data-jtb-* attributes
    const MODE_GENERIC = 'generic';              // Plain HTML

    /**
     * Parsed content result
     */
    private array $content = [];

    /**
     * CSS generated during parsing
     */
    private string $generatedCss = '';

    /**
     * Module counter for unique IDs
     */
    private int $moduleCounter = 0;

    /**
     * Style extractor instance
     */
    private JTB_Style_Extractor $styleExtractor;

    /**
     * Attribute converter instance
     */
    private JTB_Attribute_Converter $attrConverter;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->styleExtractor = new JTB_Style_Extractor();
        $this->attrConverter = new JTB_Attribute_Converter();
    }

    /**
     * Parse HTML and convert to JTB content structure
     *
     * @param string $html HTML content to parse
     * @param array $options Parsing options
     * @return array JTB content structure
     */
    public function parse(string $html, array $options = []): array
    {
        $this->content = [];
        $this->generatedCss = '';
        $this->moduleCounter = 0;

        // Determine parsing mode
        $mode = $this->detectParsingMode($html);

        // Create DOM document
        $dom = $this->createDomDocument($html);
        if (!$dom) {
            return ['error' => 'Failed to parse HTML'];
        }

        // Get body element or root element
        $body = $dom->getElementsByTagName('body')->item(0);
        if (!$body) {
            // Try to find root element
            $body = $dom->documentElement;
            if (!$body) {
                return ['error' => 'No root element found'];
            }
        }

        // Parse based on mode
        if ($mode === self::MODE_JTB_ANNOTATED) {
            $this->content = $this->parseJtbAnnotatedHtml($body, $options);
        } else {
            $this->content = $this->parseGenericHtml($body, $options);
        }

        return [
            'success' => true,
            'content' => $this->content,
            'css' => $this->generatedCss,
            'mode' => $mode,
            'stats' => [
                'modules_count' => $this->moduleCounter,
            ],
        ];
    }

    /**
     * Detect parsing mode based on HTML content
     */
    private function detectParsingMode(string $html): string
    {
        // Check for JTB data attributes
        if (preg_match('/data-jtb-module\s*=/', $html)) {
            return self::MODE_JTB_ANNOTATED;
        }

        return self::MODE_GENERIC;
    }

    /**
     * Create DOM document from HTML
     */
    private function createDomDocument(string $html): ?\DOMDocument
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');

        // Suppress libxml errors
        libxml_use_internal_errors(true);

        // Wrap in HTML structure if not present
        if (stripos($html, '<html') === false) {
            $html = '<!DOCTYPE html><html><head><meta charset="UTF-8"></head><body>' . $html . '</body></html>';
        }

        // Load HTML
        $success = $dom->loadHTML($html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

        libxml_clear_errors();

        return $success ? $dom : null;
    }

    /**
     * Parse JTB-annotated HTML (with data-jtb-* attributes)
     */
    private function parseJtbAnnotatedHtml(\DOMElement $root, array $options = []): array
    {
        $content = [];

        foreach ($root->childNodes as $child) {
            if ($child->nodeType !== XML_ELEMENT_NODE) {
                continue;
            }

            $module = $this->parseJtbAnnotatedElement($child, $options);
            if ($module) {
                $content[] = $module;
            }
        }

        return $content;
    }

    /**
     * Parse single JTB-annotated element
     */
    private function parseJtbAnnotatedElement(\DOMElement $element, array $options = []): ?array
    {
        // Get module type from data attribute
        $moduleType = $element->getAttribute('data-jtb-module');

        if (empty($moduleType)) {
            // Try to detect module type
            $mappedData = JTB_Module_Mapper::mapElement($element);
            if ($mappedData) {
                $moduleType = $mappedData['type'];
            } else {
                // Skip elements without module type that can't be detected
                // But still process children
                return $this->processChildrenAsWrapper($element, $options);
            }
        }

        // Generate unique ID
        $moduleId = $this->generateModuleId($moduleType);

        // Extract attributes
        $attrs = $this->extractModuleAttributes($element, $moduleType);

        // Extract and convert inline styles
        $styleAttrs = $this->extractStyleAttributes($element);
        $attrs = array_merge($attrs, $styleAttrs);

        // Process responsive and hover styles
        $attrs = $this->processResponsiveStyles($element, $attrs);

        // Build module structure
        $module = [
            'id' => $moduleId,
            'type' => $moduleType,
            'attrs' => $attrs,
        ];

        // Check if module has children
        if (JTB_Module_Mapper::moduleHasChildren($moduleType)) {
            $childType = JTB_Module_Mapper::getChildModuleType($moduleType);
            $module['children'] = $this->parseChildModules($element, $childType, $options);
        } elseif ($this->isStructureModule($moduleType)) {
            // Structure modules (section, row, column) always have children
            $module['children'] = $this->parseStructureChildren($element, $moduleType, $options);
        }

        $this->moduleCounter++;

        return $module;
    }

    /**
     * Parse generic HTML (without JTB annotations)
     */
    private function parseGenericHtml(\DOMElement $root, array $options = []): array
    {
        $content = [];

        foreach ($root->childNodes as $child) {
            if ($child->nodeType !== XML_ELEMENT_NODE) {
                continue;
            }

            // Check if it's a section-like element
            if (JTB_Layout_Detector::isSectionLike($child)) {
                $section = $this->parseGenericSection($child, $options);
                if ($section) {
                    $content[] = $section;
                }
            } else {
                // Wrap in section
                $section = $this->wrapInSection([$child], $options);
                if ($section) {
                    $content[] = $section;
                }
            }
        }

        return $content;
    }

    /**
     * Parse generic section element
     */
    private function parseGenericSection(\DOMElement $element, array $options = []): ?array
    {
        $sectionId = $this->generateModuleId('section');
        $sectionAttrs = $this->extractStyleAttributes($element);

        // Check for fullwidth
        $sectionAttrs['fullwidth'] = JTB_Layout_Detector::isFullwidthElement($element);

        $section = [
            'id' => $sectionId,
            'type' => 'section',
            'attrs' => $sectionAttrs,
            'children' => [],
        ];

        // Process children - find rows or create them
        $rows = $this->detectRows($element, $options);
        $section['children'] = $rows;

        $this->moduleCounter++;

        return $section;
    }

    /**
     * Detect and parse rows in section
     */
    private function detectRows(\DOMElement $element, array $options = []): array
    {
        $rows = [];
        $pendingChildren = [];

        // Tags to skip entirely
        $skipTags = ['style', 'script', 'link', 'meta', 'noscript', 'template'];

        foreach ($element->childNodes as $child) {
            if ($child->nodeType !== XML_ELEMENT_NODE) {
                continue;
            }

            $tagName = strtolower($child->nodeName);

            // Skip non-content elements
            if (in_array($tagName, $skipTags)) {
                continue;
            }

            // PRIORITY 1: Check for explicit data-jtb-module="row" attribute
            if ($child->hasAttribute('data-jtb-module') && $child->getAttribute('data-jtb-module') === 'row') {
                // Flush pending children as a row first
                if (!empty($pendingChildren)) {
                    $rows[] = $this->createRowFromElements($pendingChildren, $options);
                    $pendingChildren = [];
                }

                // Parse as row with explicit type
                $rows[] = $this->parseGenericRow($child, $options);
                continue;
            }

            // PRIORITY 2: Check if it's a row-like element by detection
            if (JTB_Layout_Detector::isRowLike($child)) {
                // Flush pending children as a row first
                if (!empty($pendingChildren)) {
                    $rows[] = $this->createRowFromElements($pendingChildren, $options);
                    $pendingChildren = [];
                }

                // Parse as row
                $rows[] = $this->parseGenericRow($child, $options);
            } else {
                // Collect for later
                $pendingChildren[] = $child;
            }
        }

        // Handle remaining children
        if (!empty($pendingChildren)) {
            $rows[] = $this->createRowFromElements($pendingChildren, $options);
        }

        return $rows;
    }

    /**
     * Parse generic row element
     */
    private function parseGenericRow(\DOMElement $element, array $options = []): array
    {
        $rowId = $this->generateModuleId('row');
        $rowAttrs = $this->extractStyleAttributes($element);

        // Detect column structure
        $columnStructure = JTB_Layout_Detector::detectColumnStructure($element);
        if ($columnStructure) {
            $rowAttrs['column_structure'] = $columnStructure;
        }

        $row = [
            'id' => $rowId,
            'type' => 'row',
            'attrs' => $rowAttrs,
            'children' => [],
        ];

        // Parse columns
        $columns = $this->detectColumns($element, $options);
        $row['children'] = $columns;

        $this->moduleCounter++;

        return $row;
    }

    /**
     * Detect and parse columns in row
     */
    private function detectColumns(\DOMElement $element, array $options = []): array
    {
        $columns = [];
        $pendingChildren = [];

        // Tags to skip entirely
        $skipTags = ['style', 'script', 'link', 'meta', 'noscript', 'template'];

        foreach ($element->childNodes as $child) {
            if ($child->nodeType !== XML_ELEMENT_NODE) {
                continue;
            }

            $tagName = strtolower($child->nodeName);

            // Skip non-content elements
            if (in_array($tagName, $skipTags)) {
                continue;
            }

            // PRIORITY 1: Check for explicit data-jtb-module="column" attribute
            if ($child->hasAttribute('data-jtb-module') && $child->getAttribute('data-jtb-module') === 'column') {
                // Flush pending children
                if (!empty($pendingChildren)) {
                    $columns[] = $this->createColumnFromElements($pendingChildren, $options);
                    $pendingChildren = [];
                }

                // Parse as column with explicit type
                $columns[] = $this->parseGenericColumn($child, $options);
                continue;
            }

            // PRIORITY 2: Check if it's a column-like element by detection
            if (JTB_Layout_Detector::isColumnLike($child)) {
                // Flush pending children
                if (!empty($pendingChildren)) {
                    $columns[] = $this->createColumnFromElements($pendingChildren, $options);
                    $pendingChildren = [];
                }

                // Parse as column
                $columns[] = $this->parseGenericColumn($child, $options);
            } else {
                // Collect for later
                $pendingChildren[] = $child;
            }
        }

        // Handle remaining children
        if (!empty($pendingChildren)) {
            $columns[] = $this->createColumnFromElements($pendingChildren, $options);
        }

        // Ensure at least one column
        if (empty($columns)) {
            $columns[] = $this->createEmptyColumn();
        }

        return $columns;
    }

    /**
     * Parse generic column element
     */
    private function parseGenericColumn(\DOMElement $element, array $options = []): array
    {
        $columnId = $this->generateModuleId('column');
        $columnAttrs = $this->extractStyleAttributes($element);

        $column = [
            'id' => $columnId,
            'type' => 'column',
            'attrs' => $columnAttrs,
            'children' => [],
        ];

        // Parse column content (modules)
        $column['children'] = $this->parseColumnContent($element, $options);

        $this->moduleCounter++;

        return $column;
    }

    /**
     * Parse column content (individual modules)
     */
    private function parseColumnContent(\DOMElement $element, array $options = []): array
    {
        $content = [];

        foreach ($element->childNodes as $child) {
            if ($child->nodeType !== XML_ELEMENT_NODE) {
                continue;
            }

            // PRIORITY 1: Check for explicit data-jtb-module attribute first
            // This takes precedence over component recognition
            if ($child->hasAttribute('data-jtb-module')) {
                $mappedData = JTB_Module_Mapper::mapElement($child);
                if ($mappedData) {
                    $module = $this->createModuleFromMapped($child, $mappedData, $options);
                    if ($module) {
                        $content[] = $module;
                        continue;
                    }
                }
            }

            // PRIORITY 2: Try to recognize as complex component (for generic HTML)
            $recognized = JTB_Component_Recognizer::recognize($child);
            if ($recognized && $recognized['confidence'] > 50) {
                $module = $this->createModuleFromRecognized($child, $recognized, $options);
                if ($module) {
                    $content[] = $module;
                    continue;
                }
            }

            // PRIORITY 3: Try module mapper for elements without data-jtb-module
            if (!$child->hasAttribute('data-jtb-module')) {
                $mappedData = JTB_Module_Mapper::mapElement($child);
                if ($mappedData) {
                    $module = $this->createModuleFromMapped($child, $mappedData, $options);
                    if ($module) {
                        $content[] = $module;
                        continue;
                    }
                }
            }

            // Fallback: wrap content in text module or skip
            $textModule = $this->createTextModuleFromElement($child);
            if ($textModule) {
                $content[] = $textModule;
            }
        }

        return $content;
    }

    /**
     * Create module from recognized component
     */
    private function createModuleFromRecognized(\DOMElement $element, array $recognized, array $options = []): ?array
    {
        $moduleType = $recognized['type'];
        $componentData = $recognized['data'] ?? [];

        $moduleId = $this->generateModuleId($moduleType);

        // Extract style attributes
        $styleAttrs = $this->extractStyleAttributes($element);

        // Merge component data with style attributes
        $attrs = array_merge($componentData, $styleAttrs);

        // Remove internal data
        unset($attrs['children']);

        $module = [
            'id' => $moduleId,
            'type' => $moduleType,
            'attrs' => $attrs,
        ];

        // Handle child modules
        if (!empty($componentData['children'])) {
            $module['children'] = [];
            foreach ($componentData['children'] as $childData) {
                $childId = $this->generateModuleId($childData['type']);
                $childModule = [
                    'id' => $childId,
                    'type' => $childData['type'],
                    'attrs' => $childData['attrs'] ?? [],
                ];

                if (!empty($childData['children'])) {
                    $childModule['children'] = $childData['children'];
                }

                $module['children'][] = $childModule;
                $this->moduleCounter++;
            }
        }

        $this->moduleCounter++;

        return $module;
    }

    /**
     * Create module from mapped data
     */
    private function createModuleFromMapped(\DOMElement $element, array $mappedData, array $options = []): ?array
    {
        $moduleType = $mappedData['type'];
        $moduleId = $this->generateModuleId($moduleType);

        // Extract style attributes
        $styleAttrs = $this->extractStyleAttributes($element);

        // Merge mapped attributes with style attributes
        $attrs = array_merge($mappedData['attrs'] ?? [], $styleAttrs);

        // Process responsive styles
        $attrs = $this->processResponsiveStyles($element, $attrs);

        $module = [
            'id' => $moduleId,
            'type' => $moduleType,
            'attrs' => $attrs,
        ];

        // Handle modules with children
        if (JTB_Module_Mapper::moduleHasChildren($moduleType)) {
            $childType = JTB_Module_Mapper::getChildModuleType($moduleType);
            $module['children'] = $this->parseChildModules($element, $childType, $options);
        }

        $this->moduleCounter++;

        return $module;
    }

    /**
     * Create text module from element
     */
    private function createTextModuleFromElement(\DOMElement $element): ?array
    {
        $tagName = strtolower($element->nodeName);

        // Skip script, style, etc
        if (in_array($tagName, ['script', 'style', 'link', 'meta', 'noscript'])) {
            return null;
        }

        $innerHTML = $this->getInnerHtml($element);

        // Skip empty elements
        if (empty(trim(strip_tags($innerHTML)))) {
            return null;
        }

        $moduleId = $this->generateModuleId('text');
        $styleAttrs = $this->extractStyleAttributes($element);

        $module = [
            'id' => $moduleId,
            'type' => 'text',
            'attrs' => array_merge([
                'content' => $innerHTML,
            ], $styleAttrs),
        ];

        $this->moduleCounter++;

        return $module;
    }

    /**
     * Parse child modules of parent
     */
    private function parseChildModules(\DOMElement $parent, string $childType, array $options = []): array
    {
        $children = [];
        $definition = JTB_Module_Mapper::getModuleDefinition($childType);

        foreach ($parent->childNodes as $child) {
            if ($child->nodeType !== XML_ELEMENT_NODE) {
                continue;
            }

            // Check if element has explicit child type
            $explicitType = $child->getAttribute('data-jtb-module');
            if ($explicitType === $childType || empty($explicitType)) {
                $childModule = $this->parseChildModule($child, $childType, $options);
                if ($childModule) {
                    $children[] = $childModule;
                }
            }
        }

        return $children;
    }

    /**
     * Parse single child module
     */
    private function parseChildModule(\DOMElement $element, string $childType, array $options = []): ?array
    {
        $childId = $this->generateModuleId($childType);

        // Get mapped data for child
        $mappedData = JTB_Module_Mapper::mapElement($element);
        $attrs = $mappedData['attrs'] ?? [];

        // Extract style attributes
        $styleAttrs = $this->extractStyleAttributes($element);
        $attrs = array_merge($attrs, $styleAttrs);

        // Process responsive styles
        $attrs = $this->processResponsiveStyles($element, $attrs);

        $module = [
            'id' => $childId,
            'type' => $childType,
            'attrs' => $attrs,
        ];

        $this->moduleCounter++;

        return $module;
    }

    /**
     * Parse structure children (for section, row, column)
     */
    private function parseStructureChildren(\DOMElement $element, string $parentType, array $options = []): array
    {
        switch ($parentType) {
            case 'section':
                return $this->detectRows($element, $options);

            case 'row':
                return $this->detectColumns($element, $options);

            case 'column':
                return $this->parseColumnContent($element, $options);

            default:
                return [];
        }
    }

    /**
     * Extract module attributes from data-jtb-attr-* attributes
     */
    private function extractModuleAttributes(\DOMElement $element, string $moduleType): array
    {
        $attrs = [];

        // Get module definition
        $definition = JTB_Module_Mapper::getModuleDefinition($moduleType);

        // Extract from data-jtb-attr-* attributes
        foreach ($element->attributes as $attr) {
            if (strpos($attr->name, 'data-jtb-attr-') === 0) {
                $attrName = substr($attr->name, 14); // Remove 'data-jtb-attr-'
                $attrName = str_replace('-', '_', $attrName);
                $attrs[$attrName] = $this->parseAttributeValue($attr->value);
            }
        }

        // Extract content-specific attributes based on module type
        if ($definition && !empty($definition['fields'])) {
            $mappedData = JTB_Module_Mapper::mapElement($element);
            if ($mappedData && !empty($mappedData['attrs'])) {
                $attrs = array_merge($mappedData['attrs'], $attrs);
            }
        }

        return $attrs;
    }

    /**
     * Extract and convert inline styles to JTB attributes
     */
    private function extractStyleAttributes(\DOMElement $element): array
    {
        // Extract styles from element
        $styles = $this->styleExtractor->extract($element);

        if (empty($styles)) {
            return [];
        }

        // Convert to JTB attributes
        return $this->attrConverter->convert($styles);
    }

    /**
     * Process responsive and hover styles from data attributes
     */
    private function processResponsiveStyles(\DOMElement $element, array $attrs): array
    {
        // Process tablet styles
        if ($element->hasAttribute('data-jtb-tablet-style')) {
            $tabletStyle = $element->getAttribute('data-jtb-tablet-style');
            $tabletStyles = $this->styleExtractor->parseStyleString($tabletStyle);
            $tabletAttrs = $this->attrConverter->convert($tabletStyles, '__tablet');

            $attrs = array_merge($attrs, $tabletAttrs);
        }

        // Process phone styles
        if ($element->hasAttribute('data-jtb-phone-style')) {
            $phoneStyle = $element->getAttribute('data-jtb-phone-style');
            $phoneStyles = $this->styleExtractor->parseStyleString($phoneStyle);
            $phoneAttrs = $this->attrConverter->convert($phoneStyles, '__phone');

            $attrs = array_merge($attrs, $phoneAttrs);
        }

        // Process hover styles
        if ($element->hasAttribute('data-jtb-hover-style')) {
            $hoverStyle = $element->getAttribute('data-jtb-hover-style');
            $hoverStyles = $this->styleExtractor->parseStyleString($hoverStyle);
            $hoverAttrs = $this->attrConverter->convert($hoverStyles, '__hover');

            $attrs = array_merge($attrs, $hoverAttrs);
        }

        // Remove internal style markers
        unset($attrs['_tablet_styles'], $attrs['_phone_styles'], $attrs['_hover_styles']);

        return $attrs;
    }

    /**
     * Create row from loose elements
     */
    private function createRowFromElements(array $elements, array $options = []): array
    {
        $rowId = $this->generateModuleId('row');

        $row = [
            'id' => $rowId,
            'type' => 'row',
            'attrs' => [],
            'children' => [],
        ];

        // Create single column for all elements
        $column = $this->createColumnFromElements($elements, $options);
        $row['children'][] = $column;

        $this->moduleCounter++;

        return $row;
    }

    /**
     * Create column from loose elements
     */
    private function createColumnFromElements(array $elements, array $options = []): array
    {
        $columnId = $this->generateModuleId('column');

        $column = [
            'id' => $columnId,
            'type' => 'column',
            'attrs' => [],
            'children' => [],
        ];

        // Parse each element as module
        foreach ($elements as $element) {
            if ($element->nodeType !== XML_ELEMENT_NODE) {
                continue;
            }

            // Try component recognizer first
            $recognized = JTB_Component_Recognizer::recognize($element);
            if ($recognized && $recognized['confidence'] > 50) {
                $module = $this->createModuleFromRecognized($element, $recognized, $options);
                if ($module) {
                    $column['children'][] = $module;
                    continue;
                }
            }

            // Try module mapper
            $mappedData = JTB_Module_Mapper::mapElement($element);
            if ($mappedData) {
                $module = $this->createModuleFromMapped($element, $mappedData, $options);
                if ($module) {
                    $column['children'][] = $module;
                    continue;
                }
            }

            // Fallback to text module
            $textModule = $this->createTextModuleFromElement($element);
            if ($textModule) {
                $column['children'][] = $textModule;
            }
        }

        $this->moduleCounter++;

        return $column;
    }

    /**
     * Create empty column
     */
    private function createEmptyColumn(): array
    {
        $columnId = $this->generateModuleId('column');

        $this->moduleCounter++;

        return [
            'id' => $columnId,
            'type' => 'column',
            'attrs' => [],
            'children' => [],
        ];
    }

    /**
     * Wrap elements in section
     */
    private function wrapInSection(array $elements, array $options = []): ?array
    {
        if (empty($elements)) {
            return null;
        }

        $sectionId = $this->generateModuleId('section');

        $section = [
            'id' => $sectionId,
            'type' => 'section',
            'attrs' => [],
            'children' => [],
        ];

        // Create row with elements
        $row = $this->createRowFromElements($elements, $options);
        $section['children'][] = $row;

        $this->moduleCounter++;

        return $section;
    }

    /**
     * Process children as wrapper (when parent doesn't have module type)
     */
    private function processChildrenAsWrapper(\DOMElement $element, array $options = []): ?array
    {
        $children = [];

        foreach ($element->childNodes as $child) {
            if ($child->nodeType !== XML_ELEMENT_NODE) {
                continue;
            }

            $module = $this->parseJtbAnnotatedElement($child, $options);
            if ($module) {
                $children[] = $module;
            }
        }

        // If only one child, return it directly
        if (count($children) === 1) {
            return $children[0];
        }

        // If multiple children, wrap in section
        if (!empty($children)) {
            return [
                'id' => $this->generateModuleId('section'),
                'type' => 'section',
                'attrs' => $this->extractStyleAttributes($element),
                'children' => $children,
            ];
        }

        return null;
    }

    /**
     * Check if module type is structure module
     */
    private function isStructureModule(string $type): bool
    {
        return in_array($type, ['section', 'row', 'column']);
    }

    /**
     * Generate unique module ID
     */
    private function generateModuleId(string $type): string
    {
        return $type . '_' . uniqid() . '_' . (++$this->moduleCounter);
    }

    /**
     * Parse attribute value (handles JSON and boolean strings)
     */
    private function parseAttributeValue(string $value)
    {
        // Try JSON decode
        $decoded = json_decode($value, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            return $decoded;
        }

        // Handle boolean strings
        if ($value === 'true') return true;
        if ($value === 'false') return false;

        // Handle numeric strings
        if (is_numeric($value)) {
            return strpos($value, '.') !== false ? (float)$value : (int)$value;
        }

        return $value;
    }

    /**
     * Get inner HTML of element
     */
    private function getInnerHtml(\DOMElement $element): string
    {
        $innerHTML = '';
        foreach ($element->childNodes as $child) {
            $innerHTML .= $element->ownerDocument->saveHTML($child);
        }
        return trim($innerHTML);
    }

    /**
     * Get generated CSS
     */
    public function getGeneratedCss(): string
    {
        return $this->generatedCss;
    }

    /**
     * Get module count
     */
    public function getModuleCount(): int
    {
        return $this->moduleCounter;
    }

    /**
     * Static method for quick parsing
     */
    public static function parseHtml(string $html, array $options = []): array
    {
        $parser = new self();
        return $parser->parse($html, $options);
    }
}
