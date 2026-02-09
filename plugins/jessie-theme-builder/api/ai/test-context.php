<?php
/**
 * Test endpoint to debug context passing
 */
namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

header('Content-Type: application/json');

// Simple test - compile a minimal AST and return the context
$testAST = [
    'goal' => 'Test',
    'style' => 'modern',
    'sections' => [
        [
            'type' => 'hero',
            'intent' => 'capture',
            'layout' => 'centered',
            'visual_weight' => 'high',
            'columns' => [
                [
                    'width' => 12,
                    'elements' => [
                        ['type' => 'headline', 'role' => 'value_proposition'],
                        ['type' => 'subheadline', 'role' => 'benefit_summary'],
                        ['type' => 'cta_primary', 'role' => 'main_action'],
                        ['type' => 'cta_secondary', 'role' => 'alternative_action'],
                        ['type' => 'body_text', 'role' => 'section_intro'],
                    ]
                ]
            ]
        ]
    ]
];

$testContext = [
    'industry' => 'technology',
    'style' => 'modern',
    'page_type' => 'landing'
];

// Compile
$sections = JTB_AI_Layout_Compiler::compile($testAST, $testContext);

// Extract first section's children's children (column's children = modules)
$result = [];
if (!empty($sections[0]['children'][0]['children'][0]['children'])) {
    foreach ($sections[0]['children'][0]['children'][0]['children'] as $idx => $module) {
        $result[] = [
            'index' => $idx,
            'type' => $module['type'],
            'text' => $module['attrs']['text'] ?? ($module['attrs']['content'] ?? null),
        ];
    }
}

echo json_encode([
    'test' => 'context_passing',
    'modules' => $result,
    'sections_count' => count($sections),
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
