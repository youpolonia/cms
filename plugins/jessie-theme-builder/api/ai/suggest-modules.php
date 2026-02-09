<?php
/**
 * JTB AI API: Suggest Modules
 * Suggests appropriate modules based on context and intent
 *
 * POST /api/jtb/ai/suggest-modules
 *
 * @package JessieThemeBuilder
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

// Require authentication (check both admin_id and user_id like router.php)
if (empty($_SESSION['admin_id']) && empty($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'Authentication required']);
    exit;
}

// Require POST method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Method not allowed']);
    exit;
}

// Note: CSRF is already validated in router.php

// Parse input
$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Invalid JSON input']);
    exit;
}

// Load AI classes
require_once dirname(dirname(__DIR__)) . '/includes/ai/class-jtb-ai-schema.php';
require_once dirname(dirname(__DIR__)) . '/includes/ai/class-jtb-ai-context.php';
require_once dirname(dirname(__DIR__)) . '/includes/ai/class-jtb-ai-generator.php';

try {
    // Get suggestion context
    $purpose = trim($input['purpose'] ?? '');
    $sectionType = trim($input['section_type'] ?? '');
    $industry = trim($input['industry'] ?? '');
    $pageType = trim($input['page_type'] ?? '');

    // Get available modules
    $allModules = JTB_AI_Schema::getCompactModuleList();
    $contentModules = JTB_AI_Schema::getContentModules();

    $suggestions = [];

    // Suggest based on purpose
    if (!empty($purpose)) {
        $purposeModules = [
            'convert' => ['cta', 'button', 'pricing_table', 'testimonial', 'countdown'],
            'inform' => ['text', 'heading', 'blurb', 'accordion', 'tabs'],
            'engage' => ['gallery', 'video', 'slider', 'testimonial', 'blog'],
            'contact' => ['contact_form', 'map', 'blurb', 'social_follow'],
            'showcase' => ['portfolio', 'gallery', 'image', 'video', 'slider'],
            'credibility' => ['testimonial', 'team_member', 'number_counter', 'social_follow']
        ];

        foreach ($purposeModules as $p => $modules) {
            if (strpos($purpose, $p) !== false) {
                foreach ($modules as $module) {
                    if (isset($contentModules[$module])) {
                        $suggestions[$module] = [
                            'slug' => $module,
                            'name' => $contentModules[$module]['name'],
                            'reason' => "Recommended for {$p}",
                            'priority' => 'high'
                        ];
                    }
                }
            }
        }
    }

    // Suggest based on section type
    if (!empty($sectionType)) {
        $sectionMapping = JTB_AI_Generator::mapSectionToModules($sectionType);

        foreach ($sectionMapping['rows'] ?? [] as $row) {
            foreach ($row['modules'] ?? [] as $columnModules) {
                if (is_array($columnModules)) {
                    foreach ($columnModules as $module) {
                        if (isset($contentModules[$module]) && !isset($suggestions[$module])) {
                            $suggestions[$module] = [
                                'slug' => $module,
                                'name' => $contentModules[$module]['name'],
                                'reason' => "Typical for {$sectionType} section",
                                'priority' => 'medium'
                            ];
                        }
                    }
                }
            }
        }
    }

    // Suggest based on industry
    if (!empty($industry)) {
        $industryContext = JTB_AI_Context::getIndustryContext($industry);
        $industryModules = $industryContext['common_modules'] ?? [];

        foreach ($industryModules as $module) {
            if (isset($contentModules[$module]) && !isset($suggestions[$module])) {
                $suggestions[$module] = [
                    'slug' => $module,
                    'name' => $contentModules[$module]['name'],
                    'reason' => "Popular in {$industry} industry",
                    'priority' => 'medium'
                ];
            }
        }
    }

    // Suggest based on page type
    if (!empty($pageType)) {
        $pageContext = JTB_AI_Context::getPageTypeContext($pageType);

        foreach ($pageContext['typical_sections'] ?? [] as $section) {
            $sectionMapping = JTB_AI_Generator::mapSectionToModules($section);

            foreach ($sectionMapping['rows'] ?? [] as $row) {
                foreach ($row['modules'] ?? [] as $columnModules) {
                    if (is_array($columnModules)) {
                        foreach ($columnModules as $module) {
                            if (isset($contentModules[$module]) && !isset($suggestions[$module])) {
                                $suggestions[$module] = [
                                    'slug' => $module,
                                    'name' => $contentModules[$module]['name'],
                                    'reason' => "Common on {$pageType} pages",
                                    'priority' => 'low'
                                ];
                            }
                        }
                    }
                }
            }
        }
    }

    // If no specific suggestions, return ALL content modules from Registry
    if (empty($suggestions)) {
        foreach ($contentModules as $module => $moduleData) {
            $suggestions[$module] = [
                'slug' => $module,
                'name' => $moduleData['name'],
                'reason' => 'Available module',
                'priority' => 'default'
            ];
        }
    }

    // Also add remaining modules as low priority so AI sees full list
    foreach ($contentModules as $module => $moduleData) {
        if (!isset($suggestions[$module])) {
            $suggestions[$module] = [
                'slug' => $module,
                'name' => $moduleData['name'],
                'reason' => 'Available module',
                'priority' => 'low'
            ];
        }
    }

    // Sort by priority
    $priorityOrder = ['high' => 1, 'medium' => 2, 'low' => 3, 'default' => 4];
    uasort($suggestions, function($a, $b) use ($priorityOrder) {
        return ($priorityOrder[$a['priority']] ?? 5) <=> ($priorityOrder[$b['priority']] ?? 5);
    });

    // Limit results
    $suggestions = array_slice($suggestions, 0, 15, true);

    // Return success response
    header('Content-Type: application/json');
    echo json_encode([
        'ok' => true,
        'suggestions' => array_values($suggestions),
        'total_modules' => count($contentModules),
        'context' => [
            'purpose' => $purpose,
            'section_type' => $sectionType,
            'industry' => $industry,
            'page_type' => $pageType
        ]
    ]);

} catch (\Exception $e) {
    error_log('JTB AI suggest-modules error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'ok' => false,
        'error' => 'An error occurred during module suggestion'
    ]);
}
