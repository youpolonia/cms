<?php
/**
 * JTB AI API: Analyze Content
 * Analyzes existing content and suggests improvements
 *
 * POST /api/jtb/ai/analyze-content
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

// Validate required fields
$pageId = (int)($input['page_id'] ?? 0);
$content = $input['content'] ?? null;

if ($pageId <= 0 && empty($content)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Either page_id or content is required']);
    exit;
}

// Load AI classes
require_once dirname(dirname(__DIR__)) . '/includes/ai/class-jtb-ai-context.php';
require_once dirname(dirname(__DIR__)) . '/includes/ai/class-jtb-ai-schema.php';
require_once dirname(dirname(__DIR__)) . '/includes/ai/class-jtb-ai-generator.php';

try {
    // Get existing content
    if ($pageId > 0 && empty($content)) {
        $existingContent = JTB_AI_Context::getExistingContent($pageId);

        if (!$existingContent['has_content']) {
            header('Content-Type: application/json');
            echo json_encode([
                'ok' => true,
                'has_content' => false,
                'message' => 'No existing content found for this page',
                'suggestions' => [
                    [
                        'type' => 'add_section',
                        'section_type' => 'hero',
                        'reason' => 'Start with a compelling hero section'
                    ],
                    [
                        'type' => 'add_section',
                        'section_type' => 'features',
                        'reason' => 'Showcase your key features'
                    ],
                    [
                        'type' => 'add_section',
                        'section_type' => 'cta',
                        'reason' => 'Include a call-to-action'
                    ]
                ]
            ]);
            exit;
        }

        $content = $existingContent;
    }

    // Analyze content structure
    $analysis = [
        'structure' => [],
        'issues' => [],
        'suggestions' => [],
        'score' => 100
    ];

    // Analyze sections
    if (is_array($content)) {
        $sections = $content['sections'] ?? $content;
        $sectionTypes = [];
        $moduleTypes = [];
        $totalModules = 0;

        foreach ($sections as $section) {
            if (($section['type'] ?? '') === 'section') {
                $sectionTypes[] = 'section';

                foreach ($section['children'] ?? [] as $row) {
                    foreach ($row['children'] ?? [] as $column) {
                        foreach ($column['children'] ?? [] as $module) {
                            $type = $module['type'] ?? '';
                            if ($type) {
                                $moduleTypes[$type] = ($moduleTypes[$type] ?? 0) + 1;
                                $totalModules++;
                            }
                        }
                    }
                }
            }
        }

        $analysis['structure'] = [
            'sections' => count($sections),
            'total_modules' => $totalModules,
            'module_breakdown' => $moduleTypes
        ];

        // Check for common issues
        $issues = [];

        // No hero section
        $hasHero = !empty($moduleTypes['fullwidth_header']) || !empty($moduleTypes['heading']);
        if (!$hasHero) {
            $issues[] = [
                'type' => 'missing_hero',
                'severity' => 'medium',
                'message' => 'No clear hero section detected. Consider adding a prominent header.'
            ];
            $analysis['score'] -= 10;
        }

        // No CTA
        $hasCTA = !empty($moduleTypes['cta']) || !empty($moduleTypes['button']);
        if (!$hasCTA) {
            $issues[] = [
                'type' => 'missing_cta',
                'severity' => 'high',
                'message' => 'No call-to-action found. Add buttons or CTA sections to guide users.'
            ];
            $analysis['score'] -= 15;
        }

        // Too few sections
        if (count($sections) < 3) {
            $issues[] = [
                'type' => 'few_sections',
                'severity' => 'low',
                'message' => 'Only ' . count($sections) . ' section(s). Consider adding more content.'
            ];
            $analysis['score'] -= 5;
        }

        // Too many sections
        if (count($sections) > 10) {
            $issues[] = [
                'type' => 'many_sections',
                'severity' => 'low',
                'message' => 'Many sections detected. Consider consolidating for better focus.'
            ];
            $analysis['score'] -= 5;
        }

        // No social proof
        $hasSocialProof = !empty($moduleTypes['testimonial']) || !empty($moduleTypes['number_counter']);
        if (!$hasSocialProof && $totalModules > 5) {
            $issues[] = [
                'type' => 'no_social_proof',
                'severity' => 'medium',
                'message' => 'No testimonials or statistics. Add social proof to build trust.'
            ];
            $analysis['score'] -= 8;
        }

        $analysis['issues'] = $issues;

        // Generate suggestions
        $suggestions = [];

        if (!$hasHero) {
            $suggestions[] = [
                'type' => 'add_section',
                'section_type' => 'hero',
                'position' => 'start',
                'reason' => 'Add a hero section with headline, subheadline, and CTA'
            ];
        }

        if (!$hasCTA) {
            $suggestions[] = [
                'type' => 'add_section',
                'section_type' => 'cta',
                'position' => 'end',
                'reason' => 'Add a call-to-action section at the end'
            ];
        }

        if (!$hasSocialProof) {
            $suggestions[] = [
                'type' => 'add_section',
                'section_type' => 'testimonials',
                'position' => 'middle',
                'reason' => 'Add testimonials or statistics for credibility'
            ];
        }

        // Check for section balance
        if (empty($moduleTypes['blurb']) && $totalModules > 3) {
            $suggestions[] = [
                'type' => 'add_section',
                'section_type' => 'features',
                'reason' => 'Consider adding feature blurbs to highlight key points'
            ];
        }

        // Suggest missing common sections
        if (empty($moduleTypes['contact_form']) && empty($moduleTypes['map'])) {
            $suggestions[] = [
                'type' => 'add_section',
                'section_type' => 'contact',
                'position' => 'end',
                'reason' => 'Add contact information or form for user inquiries'
            ];
        }

        $analysis['suggestions'] = $suggestions;
    }

    // Ensure score is within bounds
    $analysis['score'] = max(0, min(100, $analysis['score']));

    // Add overall assessment
    $analysis['assessment'] = match(true) {
        $analysis['score'] >= 90 => 'Excellent! Your page structure follows best practices.',
        $analysis['score'] >= 70 => 'Good structure with room for improvement.',
        $analysis['score'] >= 50 => 'Acceptable but consider the suggested improvements.',
        default => 'Significant improvements recommended for better user experience.'
    };

    // Return success response
    header('Content-Type: application/json');
    echo json_encode([
        'ok' => true,
        'page_id' => $pageId,
        'analysis' => $analysis
    ]);

} catch (\Exception $e) {
    error_log('JTB AI analyze-content error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'ok' => false,
        'error' => 'An error occurred during content analysis'
    ]);
}
