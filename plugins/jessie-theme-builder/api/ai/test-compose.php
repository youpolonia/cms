<?php
/**
 * Debug endpoint for compose-layout - STEP BY STEP
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

// Clean buffers
while (ob_get_level()) ob_end_clean();
ob_start();

header('Content-Type: application/json');

$step = $_GET['step'] ?? 'full';
$startTime = microtime(true);

try {
    $result = ['ok' => true, 'step' => $step];

    if ($step === '1' || $step === 'full') {
        // Step 1: Just test composePage
        $patterns = JTB_AI_Composer::composePage('saas_landing', ['style' => 'modern']);
        $result['patterns_count'] = count($patterns);
        $result['first_pattern'] = $patterns[0]['pattern'] ?? 'unknown';
        $result['step1_time'] = round((microtime(true) - $startTime) * 1000) . 'ms';

        if ($step === '1') {
            $output = ob_get_clean();
            $result['buffer'] = $output ? substr($output, 0, 200) : null;
            echo json_encode($result);
            exit;
        }
    }

    if ($step === '2' || $step === 'full') {
        // Step 2: Test renderPattern directly with a simple hero pattern
        $simplePattern = [
            'pattern' => 'hero_asymmetric',
            'variant' => 'default',
            'attrs' => ['visual_context' => 'LIGHT'],
            'rows' => [
                [
                    'columns' => [
                        ['width' => '1_1', 'modules' => [['type' => 'heading', 'attrs' => ['text' => 'Test']]]]
                    ]
                ]
            ]
        ];
        $section = JTB_AI_Pattern_Renderer::renderPattern($simplePattern, ['style' => 'modern']);
        $result['section_type'] = $section['type'] ?? 'missing';
        $result['section_children'] = count($section['children'] ?? []);
        $result['step2_time'] = round((microtime(true) - $startTime) * 1000) . 'ms';

        if ($step === '2') {
            $output = ob_get_clean();
            $result['buffer'] = $output ? substr($output, 0, 200) : null;
            echo json_encode($result);
            exit;
        }
    }

    if ($step === '3') {
        // Step 3: Test renderPage with real patterns - with error catching
        $phpErrors = [];
        set_error_handler(function($severity, $message, $file, $line) use (&$phpErrors) {
            $phpErrors[] = basename($file) . ':' . $line . ' - ' . $message;
            return true;
        });

        $patterns = JTB_AI_Composer::composePage('saas_landing', ['style' => 'modern']);
        $result['patterns_count'] = count($patterns);
        $result['patterns_info'] = array_map(fn($p) => $p['pattern'] ?? 'unknown', $patterns);

        // Try rendering first pattern only
        $firstPattern = $patterns[0] ?? null;
        if ($firstPattern) {
            $result['first_pattern_keys'] = array_keys($firstPattern);
            $result['first_pattern_attrs'] = array_keys($firstPattern['attrs'] ?? []);

            try {
                $section = JTB_AI_Pattern_Renderer::renderPattern($firstPattern, ['style' => 'modern']);
                $result['first_section_type'] = $section['type'] ?? 'missing';
                $result['first_section_children'] = count($section['children'] ?? []);
            } catch (\Throwable $e) {
                $result['render_error'] = $e->getMessage() . ' at ' . basename($e->getFile()) . ':' . $e->getLine();
            }
        }

        restore_error_handler();
        $result['php_errors'] = $phpErrors;
        $result['step3_time'] = round((microtime(true) - $startTime) * 1000) . 'ms';

        $output = ob_get_clean();
        $result['buffer'] = $output ? substr($output, 0, 200) : null;
        echo json_encode($result);
        exit;
    }

    if ($step === '5') {
        // Step 5: Test generateComposedLayout (without validation/autofix)
        $phpErrors = [];
        set_error_handler(function($severity, $message, $file, $line) use (&$phpErrors) {
            $phpErrors[] = basename($file) . ':' . $line . ' - ' . $message;
            return true;
        });

        try {
            $layout = JTB_AI_Generator::generateComposedLayout('technology landing page', ['style' => 'modern']);
            $result['ok'] = $layout['ok'] ?? false;
            $result['sections_count'] = count($layout['sections'] ?? []);
            $result['patterns_used'] = $layout['patterns_used'] ?? [];
        } catch (\Throwable $e) {
            $result['ok'] = false;
            $result['error'] = $e->getMessage();
            $result['file'] = basename($e->getFile()) . ':' . $e->getLine();
        }

        restore_error_handler();
        $result['php_errors'] = $phpErrors;
        $result['step5_time'] = round((microtime(true) - $startTime) * 1000) . 'ms';

        $output = ob_get_clean();
        $result['buffer'] = $output ? substr($output, 0, 200) : null;
        echo json_encode($result);
        exit;
    }

    if ($step === '6') {
        // Step 6: Test validation separately (without autofix)
        $phpErrors = [];
        set_error_handler(function($severity, $message, $file, $line) use (&$phpErrors) {
            $phpErrors[] = basename($file) . ':' . $line . ' - ' . $message;
            return true;
        });

        try {
            // First get a layout
            $layout = JTB_AI_Generator::generateComposedLayout('technology landing page', ['style' => 'modern']);
            $result['layout_ok'] = $layout['ok'] ?? false;
            $result['sections_count'] = count($layout['sections'] ?? []);

            // Now try validation
            $patternsForValidation = [];
            foreach ($layout['sections'] ?? [] as $index => $section) {
                $patternsForValidation[] = [
                    'pattern' => $section['_pattern'] ?? 'unknown',
                    'attrs' => $section['attrs'] ?? [],
                    'children' => $section['children'] ?? []
                ];
            }

            $validation = JTB_AI_Generator::validateLayout(['patterns' => $patternsForValidation]);
            $result['validation_score'] = $validation['score'] ?? null;
            $result['validation_status'] = $validation['status'] ?? null;
            $result['violations_count'] = count($validation['violations'] ?? []);

        } catch (\Throwable $e) {
            $result['ok'] = false;
            $result['error'] = $e->getMessage();
            $result['file'] = basename($e->getFile()) . ':' . $e->getLine();
        }

        restore_error_handler();
        $result['php_errors'] = $phpErrors;
        $result['step6_time'] = round((microtime(true) - $startTime) * 1000) . 'ms';

        $output = ob_get_clean();
        $result['buffer'] = $output ? substr($output, 0, 200) : null;
        echo json_encode($result);
        exit;
    }

    if ($step === '7') {
        // Step 7: Test AutoFix separately
        $phpErrors = [];
        set_error_handler(function($severity, $message, $file, $line) use (&$phpErrors) {
            $phpErrors[] = basename($file) . ':' . $line . ' - ' . $message;
            return true;
        });

        try {
            $layout = JTB_AI_Generator::generateComposedLayout('technology landing page', ['style' => 'modern']);
            $result['layout_ok'] = $layout['ok'] ?? false;
            $result['before_sections'] = count($layout['sections'] ?? []);

            // Add fake quality data for autofix
            $layout['_quality'] = [
                'score' => 15,
                'status' => 'GOOD',
                'violations' => [],
                'warnings' => []
            ];

            // Try autofix
            $fixed = JTB_AI_AutoFix::applyFixes($layout, $layout['_quality']);
            $result['autofix_count'] = $fixed['_autofix_count'] ?? 0;
            $result['after_sections'] = count($fixed['sections'] ?? []);

        } catch (\Throwable $e) {
            $result['ok'] = false;
            $result['error'] = $e->getMessage();
            $result['file'] = basename($e->getFile()) . ':' . $e->getLine();
        }

        restore_error_handler();
        $result['php_errors'] = $phpErrors;
        $result['step7_time'] = round((microtime(true) - $startTime) * 1000) . 'ms';

        $output = ob_get_clean();
        $result['buffer'] = $output ? substr($output, 0, 200) : null;
        echo json_encode($result);
        exit;
    }

    if ($step === 'full' || $step === '4') {
        // Full test - render patterns one by one to find which one fails
        $phpErrors = [];
        set_error_handler(function($severity, $message, $file, $line) use (&$phpErrors) {
            $phpErrors[] = basename($file) . ':' . $line . ' - ' . $message;
            return true;
        });

        $patterns = JTB_AI_Composer::composePage('saas_landing', ['style' => 'modern']);
        $result['patterns_count'] = count($patterns);

        $sections = [];
        $failedPattern = null;
        foreach ($patterns as $index => $pattern) {
            try {
                $section = JTB_AI_Pattern_Renderer::renderPattern($pattern, ['style' => 'modern']);
                $sections[] = $section;
            } catch (\Throwable $e) {
                $failedPattern = [
                    'index' => $index,
                    'pattern' => $pattern['pattern'] ?? 'unknown',
                    'error' => $e->getMessage(),
                    'file' => basename($e->getFile()) . ':' . $e->getLine()
                ];
                break;
            }
        }

        restore_error_handler();
        $result['sections_count'] = count($sections);
        $result['failed_pattern'] = $failedPattern;
        $result['php_errors'] = $phpErrors;
        $result['total_time'] = round((microtime(true) - $startTime) * 1000) . 'ms';
    }

    $output = ob_get_clean();
    $result['buffer'] = $output ? substr($output, 0, 200) : null;
    echo json_encode($result);

} catch (\Throwable $e) {
    $output = ob_get_clean();
    http_response_code(500);
    echo json_encode([
        'ok' => false,
        'error' => $e->getMessage(),
        'file' => basename($e->getFile()) . ':' . $e->getLine(),
        'buffer' => $output ? substr($output, 0, 200) : null,
    ]);
}
