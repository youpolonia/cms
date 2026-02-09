<?php
/**
 * Render API Endpoint
 * POST /api/jtb/render
 *
 * Renders JTB content to HTML. For preview purposes, validation is relaxed
 * to handle AI-generated content that may not be perfectly structured.
 *
 * @package JessieThemeBuilder
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

header('Content-Type: application/json');

// Verify request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jtb_json_response(false, [], 'Method not allowed', 405);
    exit;
}

/**
 * Recursively ensure all elements have IDs and proper structure
 */
function normalizeElement($element, $prefix, $depth = 0) {
    if (!is_array($element)) {
        return $element;
    }

    // Add ID if missing
    if (isset($element['type']) && empty($element['id'])) {
        $element['id'] = $prefix . '_' . uniqid();
    }

    // Normalize children recursively
    if (isset($element['children']) && is_array($element['children'])) {
        $element['children'] = array_map(function($child, $i) use ($prefix, $depth) {
            return normalizeElement($child, $prefix . '_c' . $i, $depth + 1);
        }, $element['children'], array_keys($element['children']));
    }

    return $element;
}

/**
 * Wrap orphan modules in proper structure (section > row > column)
 * AI sometimes generates modules directly without proper nesting
 */
function wrapOrphanModules($content) {
    $structuralTypes = ['section', 'row', 'column'];
    $wrapped = [];

    foreach ($content as $index => $element) {
        if (!isset($element['type'])) {
            continue;
        }

        $type = $element['type'];

        // Already a section - normalize its children
        if ($type === 'section') {
            $element = normalizeSection($element);
            $wrapped[] = $element;
        }
        // Row without section - wrap in section
        elseif ($type === 'row') {
            $wrapped[] = [
                'type' => 'section',
                'id' => 'auto_section_' . $index . '_' . uniqid(),
                'attrs' => [],
                'children' => [normalizeRow($element)]
            ];
        }
        // Column without row - wrap in section > row
        elseif ($type === 'column') {
            $wrapped[] = [
                'type' => 'section',
                'id' => 'auto_section_' . $index . '_' . uniqid(),
                'attrs' => [],
                'children' => [
                    [
                        'type' => 'row',
                        'id' => 'auto_row_' . $index . '_' . uniqid(),
                        'attrs' => ['columns' => '1'],
                        'children' => [normalizeColumn($element)]
                    ]
                ]
            ];
        }
        // Module without any wrapper - wrap in section > row > column
        else {
            $wrapped[] = [
                'type' => 'section',
                'id' => 'auto_section_' . $index . '_' . uniqid(),
                'attrs' => [],
                'children' => [
                    [
                        'type' => 'row',
                        'id' => 'auto_row_' . $index . '_' . uniqid(),
                        'attrs' => ['columns' => '1'],
                        'children' => [
                            [
                                'type' => 'column',
                                'id' => 'auto_column_' . $index . '_' . uniqid(),
                                'attrs' => [],
                                'children' => [$element]
                            ]
                        ]
                    ]
                ]
            ];
        }
    }

    return $wrapped;
}

function normalizeSection($section) {
    if (!isset($section['id'])) {
        $section['id'] = 'section_' . uniqid();
    }

    if (isset($section['children']) && is_array($section['children'])) {
        $normalizedChildren = [];
        foreach ($section['children'] as $i => $child) {
            if (!isset($child['type'])) continue;

            if ($child['type'] === 'row') {
                $normalizedChildren[] = normalizeRow($child);
            } else {
                // Non-row child in section - wrap in row > column
                $normalizedChildren[] = [
                    'type' => 'row',
                    'id' => 'auto_row_' . $i . '_' . uniqid(),
                    'attrs' => ['columns' => '1'],
                    'children' => [
                        [
                            'type' => 'column',
                            'id' => 'auto_col_' . $i . '_' . uniqid(),
                            'attrs' => [],
                            'children' => [$child]
                        ]
                    ]
                ];
            }
        }
        $section['children'] = $normalizedChildren;
    }

    return $section;
}

function normalizeRow($row) {
    if (!isset($row['id'])) {
        $row['id'] = 'row_' . uniqid();
    }

    if (isset($row['children']) && is_array($row['children'])) {
        $normalizedChildren = [];
        foreach ($row['children'] as $i => $child) {
            if (!isset($child['type'])) continue;

            if ($child['type'] === 'column') {
                $normalizedChildren[] = normalizeColumn($child);
            } else {
                // Non-column child in row - wrap in column
                $normalizedChildren[] = [
                    'type' => 'column',
                    'id' => 'auto_col_' . $i . '_' . uniqid(),
                    'attrs' => [],
                    'children' => [$child]
                ];
            }
        }
        $row['children'] = $normalizedChildren;
    }

    return $row;
}

function normalizeColumn($column) {
    if (!isset($column['id'])) {
        $column['id'] = 'column_' . uniqid();
    }

    // Recursively normalize children (modules can have children too, like accordion)
    if (isset($column['children']) && is_array($column['children'])) {
        $column['children'] = array_map(function($child, $i) {
            if (is_array($child) && !isset($child['id']) && isset($child['type'])) {
                $child['id'] = $child['type'] . '_' . $i . '_' . uniqid();
            }
            // Recursively handle nested children
            if (isset($child['children']) && is_array($child['children'])) {
                $child['children'] = array_map(function($nested, $j) {
                    if (is_array($nested) && !isset($nested['id']) && isset($nested['type'])) {
                        $nested['id'] = $nested['type'] . '_' . $j . '_' . uniqid();
                    }
                    return $nested;
                }, $child['children'], array_keys($child['children']));
            }
            return $child;
        }, $column['children'], array_keys($column['children']));
    }

    return $column;
}

/**
 * Normalize AI-generated format to Renderer format.
 * AI generates: {type, padding, background_color, content: [...]}
 * Renderer expects: {type, attrs: {padding, background_color}, children: [...]}
 *
 * This function recursively:
 * 1. Renames 'content' array to 'children' (when it contains nested elements)
 * 2. Moves flat attributes into 'attrs' object
 * 3. Preserves 'type', 'id', 'children', 'attrs' at top level
 */
function normalizeAIFormat($element) {
    if (!is_array($element) || empty($element['type'])) {
        return $element;
    }

    // Step 1: If 'content' key contains an array of elements (not a string), rename to 'children'
    if (isset($element['content']) && is_array($element['content']) && !empty($element['content'])) {
        // Check if it's an array of elements (has 'type' keys) vs a text content string
        $firstItem = reset($element['content']);
        if (is_array($firstItem) && isset($firstItem['type'])) {
            // It's nested elements — move to 'children'
            if (!isset($element['children'])) {
                $element['children'] = $element['content'];
            }
            unset($element['content']);
        }
    }

    // Step 2: Move flat attributes into 'attrs' object
    // Keys that should stay at top level
    $topLevelKeys = ['type', 'id', 'children', 'attrs', '_section_type', '_role', '_pattern'];

    if (!isset($element['attrs'])) {
        $element['attrs'] = [];
    }

    foreach ($element as $key => $value) {
        if (!in_array($key, $topLevelKeys)) {
            // Move to attrs
            $element['attrs'][$key] = $value;
            unset($element[$key]);
        }
    }

    // Step 3: Recursively normalize children
    if (isset($element['children']) && is_array($element['children'])) {
        $element['children'] = array_map(function($child) {
            return normalizeAIFormat($child);
        }, $element['children']);
    }

    return $element;
}

// Get raw input
$rawInput = file_get_contents('php://input');
$contentArray = null;

// Try JSON body first
if (!empty($rawInput)) {
    $jsonInput = json_decode($rawInput, true);
    if (json_last_error() === JSON_ERROR_NONE && isset($jsonInput['content'])) {
        $contentArray = $jsonInput['content'];
    }
}

// Fallback to POST form data
if ($contentArray === null && isset($_POST['content'])) {
    $content = $_POST['content'];
    if (is_string($content)) {
        $contentArray = json_decode($content, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            jtb_json_response(false, [], 'Invalid JSON content: ' . json_last_error_msg(), 400);
            exit;
        }
    } elseif (is_array($content)) {
        $contentArray = $content;
    }
}

if (empty($contentArray)) {
    jtb_json_response(false, [], 'Content is required', 400);
    exit;
}

// Ensure version exists
if (!isset($contentArray['version'])) {
    $contentArray['version'] = '1.0';
}

// Normalize AI-generated format: content→children, flat attrs→attrs object
if (isset($contentArray['content']) && is_array($contentArray['content'])) {
    if (class_exists(__NAMESPACE__ . '\\JTB_AI_Normalizer')) {
        $contentArray['content'] = JTB_AI_Normalizer::convertAISections($contentArray['content']);
    } else {
        // Fallback: use local normalizeAIFormat function
        $contentArray['content'] = array_map(function($el) {
            return normalizeAIFormat($el);
        }, $contentArray['content']);
    }
}

// Normalize structure - fix AI-generated content issues
if (isset($contentArray['content']) && is_array($contentArray['content'])) {
    $contentArray['content'] = wrapOrphanModules($contentArray['content']);
}

// Render content directly without strict validation (for preview)
try {
    set_error_handler(function($errno, $errstr, $errfile, $errline) {
        throw new \ErrorException($errstr . " in $errfile:$errline", $errno);
    });

    $html = JTB_Renderer::render($contentArray);
    $css = JTB_Renderer::getCss();

    restore_error_handler();

    jtb_json_response(true, [
        'html' => $html,
        'css' => $css
    ]);
} catch (\Exception $e) {
    jtb_json_response(false, [], 'Render error: ' . $e->getMessage(), 500);
}
