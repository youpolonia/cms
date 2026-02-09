<?php
/**
 * JTB AI Generator
 * Main layout and content generation engine
 * Parses prompts, builds layouts, and generates module content
 *
 * @package JessieThemeBuilder
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

class JTB_AI_Generator
{
    // ========================================
    // Main Generation Methods
    // ========================================

    /**
     * Generate complete page layout from prompt
     * @param string $prompt User's description of desired page
     * @param array $options Generation options
     * @return array Generated layout or error
     */
    public static function generateLayout(string $prompt, array $options = []): array
    {
        // Parse the user's intent
        $intent = self::parseSemanticIntent($prompt);

        // Get context
        $pageId = $options['page_id'] ?? 0;
        $context = [];

        // Always set style from intent (detected from prompt)
        $context['style'] = $intent['style'] ?: 'modern';
        $context['page_type'] = $intent['page_type'];
        $context['industry'] = $intent['industry'];
        $context['tone'] = $intent['tone'];

        if ($pageId > 0) {
            $pageContext = JTB_AI_Context::getPageContext($pageId);
            $siteContext = JTB_AI_Context::getSiteContext();
            $brandContext = JTB_AI_Context::getBrandingContext();

            $context = array_merge($context, [
                'page_title' => $pageContext['title'] ?? '',
                'site_name' => $siteContext['name'] ?? '',
                'industry' => $intent['industry'] ?: ($siteContext['industry'] ?? $context['industry']),
                'colors' => [
                    'primary' => $brandContext['primary_color'],
                    'secondary' => $brandContext['secondary_color'],
                    'accent' => $brandContext['accent_color']
                ]
            ]);
        }

        // Merge with user options
        $context = array_merge($context, $options);

        // Determine sections needed
        $sections = $intent['sections'];
        if (empty($sections)) {
            $sections = self::getSectionsForPageType($intent['page_type']);
        }

        // Build layout structure
        $layout = [];
        foreach ($sections as $index => $sectionType) {
            $section = self::generateSection($sectionType, array_merge($context, [
                'section_index' => $index,
                'total_sections' => count($sections)
            ]));

            if (!empty($section)) {
                $layout[] = $section;
            }
        }

        // Apply branding
        if (!empty($options['apply_branding']) && !empty($context['colors'])) {
            $layout = self::applyBranding($layout, $context['colors']);
        }

        // Validate output
        if (!self::validateOutput(['sections' => $layout])) {
            return [
                'ok' => false,
                'error' => 'Generated layout failed validation',
                'sections' => $layout
            ];
        }

        return [
            'ok' => true,
            'sections' => $layout,
            'intent' => $intent,
            'context' => $context
        ];
    }

    /**
     * Generate a single section
     * @param string $sectionType Type of section
     * @param array $context Context data
     * @return array Section structure
     */
    public static function generateSection(string $sectionType, array $context = []): array
    {
        // Get section mapping
        $mapping = self::mapSectionToModules($sectionType);

        // Add section type to context for content generation
        $sectionContext = array_merge($context, [
            'section_type' => $sectionType,
            'purpose' => $sectionType  // Used by headline generator
        ]);

        // Get section attrs (includes _pattern and _visual_context)
        $sectionAttrs = self::getSectionAttrs($sectionType, $sectionContext);

        // Build section structure with _pattern at root level for AutoFix
        $section = [
            'id' => self::generateId('section'),
            'type' => 'section',
            // CRITICAL: Pattern at root level for AutoFix detection (Stages 11-17)
            '_pattern' => $sectionType,
            '_visual_context' => $sectionAttrs['_visual_context'] ?? 'LIGHT',
            'attrs' => $sectionAttrs,
            'children' => []
        ];

        // Generate rows based on mapping
        foreach ($mapping['rows'] as $rowIndex => $rowConfig) {
            // Add row context
            $rowContext = array_merge($sectionContext, [
                'row_index' => $rowIndex,
                'is_first_row' => $rowIndex === 0
            ]);

            $row = self::generateRow(
                $rowConfig['columns'],
                $rowConfig['modules'],
                $rowContext
            );
            if (!empty($row)) {
                $section['children'][] = $row;
            }
        }

        return $section;
    }

    /**
     * Generate a row with columns and modules
     * @param string $columnLayout Column layout string (e.g., "1_2_1_2")
     * @param array $moduleTypes Modules for each column
     * @param array $context Context data
     * @return array Row structure
     */
    public static function generateRow(string $columnLayout, array $moduleTypes, array $context = []): array
    {
        $style = $context['style'] ?? 'modern';

        // Get row styling from JTB_AI_Styles
        $rowAttrs = JTB_AI_Styles::getRowAttrs($style, $context);
        $rowAttrs['columns'] = $columnLayout;

        $row = [
            'id' => self::generateId('row'),
            'type' => 'row',
            'attrs' => $rowAttrs,
            'children' => []
        ];

        // Parse column widths
        $widths = self::parseColumnLayout($columnLayout);

        // Track module indices for varied content generation
        $moduleCounters = [];

        // Determine if columns should have card styling
        // Dynamically detect card-style modules: those with both title/name AND content/description fields
        $cardModuleTypes = self::getCardStyleModuleTypes();

        foreach ($widths as $colIndex => $width) {
            // Get modules for this column to determine if it needs card styling
            $modules = $moduleTypes[$colIndex] ?? [];
            if (!is_array($modules)) {
                $modules = [$modules];
            }

            // Check if this column has card-style modules
            $hasCardModule = false;
            foreach ($modules as $moduleType) {
                if (in_array($moduleType, $cardModuleTypes)) {
                    $hasCardModule = true;
                    break;
                }
            }

            // Get column styling from JTB_AI_Styles
            $columnContext = [
                'width' => $width,
                'is_card' => $hasCardModule && count($modules) === 1, // Card styling only for single-module columns
            ];
            $colAttrs = JTB_AI_Styles::getColumnAttrs($style, $columnContext);

            $column = [
                'id' => self::generateId('column'),
                'type' => 'column',
                'attrs' => $colAttrs,
                'children' => []
            ];

            foreach ($modules as $moduleType) {
                if (empty($moduleType)) continue;

                // Track index for each module type to generate varied content
                if (!isset($moduleCounters[$moduleType])) {
                    $moduleCounters[$moduleType] = 0;
                }

                // Create context with module-specific index for varied content
                $moduleContext = array_merge($context, [
                    'blurb_index' => $moduleCounters[$moduleType],
                    'testimonial_index' => $moduleCounters[$moduleType],
                    'pricing_index' => $moduleCounters[$moduleType],
                    'team_index' => $moduleCounters[$moduleType],
                    'counter_index' => $moduleCounters[$moduleType],
                    'faq_index' => $moduleCounters[$moduleType],
                    'module_index' => $moduleCounters[$moduleType]
                ]);

                $module = self::generateModule($moduleType, $moduleContext);
                if (!empty($module)) {
                    $column['children'][] = $module;
                    $moduleCounters[$moduleType]++;
                }
            }

            $row['children'][] = $column;
        }

        return $row;
    }

    /**
     * Generate a single module with content
     * @param string $moduleType Module type
     * @param array $context Context data
     * @return array Module structure
     */
    public static function generateModule(string $moduleType, array $context = []): array
    {
        // Check if module exists
        if (!JTB_Registry::exists($moduleType)) {
            return [];
        }

        // Generate content only - do NOT include design field defaults
        // Design fields (transform, animation, etc.) should use builder defaults
        $content = JTB_AI_Content::generateModuleContent($moduleType, $context);

        return [
            'id' => self::generateId($moduleType),
            'type' => $moduleType,
            'attrs' => $content,
            'children' => []
        ];
    }

    // ========================================
    // AST-BASED LAYOUT GENERATION (NEW!)
    // ========================================

    /**
     * Generate layout using AI-driven AST pipeline
     *
     * THIS IS THE NEW METHOD that actually sends layout decisions to AI
     * instead of using hardcoded pattern sequences.
     *
     * Flow:
     * 1. AI generates Layout AST (abstract structure)
     * 2. Compiler transforms AST to JTB JSON
     * 3. AutoFix adds Stage 11-17 attributes
     *
     * @param string $prompt User's description of desired page
     * @param array $options Generation options
     * @return array Generated layout
     */
    public static function generateASTLayout(string $prompt, array $options = []): array
    {
        // DEBUG: Log AST layout generation entry
        file_put_contents('/tmp/jtb_debug_flow.log', "[generateASTLayout] ENTRY - prompt: " . substr($prompt, 0, 50) . "...\n", FILE_APPEND);

        // Parse basic intent for context (still useful for fallback)
        $intent = self::parseSemanticIntent($prompt);

        // Build context
        $context = [
            'style' => $intent['style'] ?: ($options['style'] ?? 'modern'),
            'industry' => $intent['industry'] ?: ($options['industry'] ?? ''),
            'page_type' => $intent['page_type'] ?: ($options['page_type'] ?? 'landing'),
            'tone' => $intent['tone'] ?: ($options['tone'] ?? 'professional'),
            'site_name' => $options['site_name'] ?? '',
            // AI provider and model selection (from config, no hardcodes)
            'ai_provider' => $options['ai_provider'] ?? self::getDefaultProvider(),
            'ai_model' => $options['ai_model'] ?? null, // null = use provider's default
        ];

        // PHASE 1: Generate Layout AST via AI
        // THIS IS WHERE AI ACTUALLY MAKES LAYOUT DECISIONS
        $astResult = JTB_AI_Layout_Engine::generateLayoutAST($prompt, $context);

        if (!$astResult['ok']) {
            return [
                'ok' => false,
                'error' => 'Failed to generate layout AST',
                'sections' => [],
                'source' => 'ast_failed',
            ];
        }

        $ast = $astResult['ast'];

        // PHASE 2: Compile AST to JTB structure
        // Pure transformation, no AI logic
        $sections = JTB_AI_Layout_Compiler::compile($ast, $context);

        if (empty($sections)) {
            return [
                'ok' => false,
                'error' => 'AST compilation produced no sections',
                'sections' => [],
                'ast' => $ast,
                'source' => 'compile_failed',
            ];
        }

        // Build layout response
        $layout = [
            'ok' => true,
            'sections' => $sections,
            'intent' => $intent,
            'context' => $context,
            'ast' => $ast,
            'source' => $astResult['source'], // 'ai' or 'fallback'
            'provider' => $astResult['provider'] ?? 'unknown',
            'model' => $astResult['model'] ?? null,
            'tokens_used' => $astResult['tokens_used'] ?? 0,
            'ast_validation' => $astResult['validation'] ?? null,
            // Map patterns for compatibility
            'composition_intent' => self::mapToCompositionIntent($intent, $options),
            'patterns_used' => array_map(fn($s) => $s['_pattern'] ?? 'unknown', $sections),
        ];

        return $layout;
    }

    // ========================================
    // COMPOSITIONAL LAYOUT GENERATION
    // ========================================

    /**
     * Generate layout with automatic quality validation, auto-fix, confidence scoring, and retry
     * Includes anti-oscillation detection and stop conditions
     *
     * Now supports AST pipeline via 'use_ast' option.
     *
     * @param array $input Generation input (prompt, options)
     * @return array Layout with _quality metadata including confidence
     */
    public static function generateWithValidation(array $input): array
    {
        // DEBUG: Entry point
        file_put_contents('/tmp/jtb_debug_flow.log', "[generateWithValidation] ENTRY\n", FILE_APPEND);

        $maxAttempts = 3;
        $attempts = 0;
        $bestResult = null;
        $bestScore = -1;
        $previousQuality = null;

        $prompt = $input['prompt'] ?? '';
        $options = $input['options'] ?? [];

        // CHECK FOR AST MODE
        $useAST = $options['use_ast'] ?? false;

        // DEBUG: Log AST mode decision
        file_put_contents('/tmp/jtb_debug_flow.log', "[generateWithValidation] useAST=" . var_export($useAST, true) . ", options[use_ast]=" . var_export($options['use_ast'] ?? 'NOT_SET', true) . "\n", FILE_APPEND);

        if ($useAST) {
            file_put_contents('/tmp/jtb_debug_flow.log', "[generateWithValidation] ENTERING AST PIPELINE! useAST=true\n", FILE_APPEND);
            // Use new AST pipeline
            $layout = self::generateASTLayout($prompt, $options);

            if (!($layout['ok'] ?? false)) {
                return $layout;
            }

            // Still apply AutoFix for Stage 11-17 attributes
            $patternsForValidation = self::extractPatternsForValidation($layout);
            $validation = self::validateLayout(['patterns' => $patternsForValidation]);

            $layout['_quality'] = [
                'score' => $validation['score'],
                'status' => $validation['status'],
                'breakdown' => $validation['breakdown'],
                'violations' => $validation['violations'],
                'warnings' => $validation['warnings'],
                'meta' => $validation['meta'],
                'metrics' => $validation['metrics'] ?? [],
                'attempt' => 1,
                'forced_accept' => false,
                'autofix_applied' => false,
                'source' => $layout['source'] ?? 'ast',
                'provider' => $layout['provider'] ?? 'unknown',
            ];

            // Apply AutoFix
            $fixedLayout = JTB_AI_AutoFix::applyFixes($layout, $layout['_quality']);
            $fixCount = $fixedLayout['_autofix_count'] ?? 0;

            if ($fixCount > 0) {
                $layout = $fixedLayout;
                $layout['_quality']['autofix_applied'] = true;
                $layout['_quality']['autofix_rules'] = JTB_AI_AutoFix::getAppliedFixes();

                // Re-validate
                $patternsForValidation = self::extractPatternsForValidation($layout);
                $validationAfter = self::validateLayout(['patterns' => $patternsForValidation]);
                $layout['_quality']['score'] = $validationAfter['score'];
                $layout['_quality']['status'] = $validationAfter['status'];
                $layout['_quality']['violations'] = $validationAfter['violations'];
                $layout['_quality']['warnings'] = $validationAfter['warnings'];
            }

            // Set confidence for AST layouts
            $layout['_quality']['confidence'] = JTB_AI_Confidence::hasCriticalViolations($layout['_quality']['violations'] ?? []) ? 0.5 : 0.9;
            $layout['_quality']['decision'] = 'ACCEPT';
            $layout['_quality']['stop_reason'] = 'AST_SINGLE_PASS';

            self::logQualityDecision($layout['_quality']);
            return $layout;
        }

        // LEGACY PATH: Use old compositional system
        $layoutHashHistory = []; // For oscillation detection

        while ($attempts < $maxAttempts) {
            $attempts++;

            $currentOptions = $options;
            if ($attempts > 1 && $previousQuality !== null) {
                $feedbackBlock = JTB_AI_Prompts::buildQualityFeedbackPrompt($previousQuality, $attempts);
                $currentOptions['_quality_feedback'] = $feedbackBlock;
            }

            $layout = self::generateComposedLayout($prompt, $currentOptions);

            if (!($layout['ok'] ?? false)) {
                continue;
            }

            // Validate BEFORE autofix
            $patternsForValidation = self::extractPatternsForValidation($layout);
            $validationBefore = self::validateLayout(['patterns' => $patternsForValidation]);

            // Generate hash before autofix
            $hashBeforeFix = JTB_AI_Confidence::generateLayoutHash($layout);

            $layout['_quality'] = [
                'score' => $validationBefore['score'],
                'status' => $validationBefore['status'],
                'breakdown' => $validationBefore['breakdown'],
                'violations' => $validationBefore['violations'],
                'warnings' => $validationBefore['warnings'],
                'meta' => $validationBefore['meta'],
                'metrics' => $validationBefore['metrics'] ?? [],
                'attempt' => $attempts,
                'forced_accept' => false,
                'autofix_applied' => false,
                'layout_hash' => $hashBeforeFix,
            ];

            $qualityBefore = $layout['_quality'];

            // ============================================================
            // AUTO-FIX ENGINE: Apply deterministic fixes
            // ALWAYS run AutoFix to add Stage 11-17 attributes
            // (visual_intent, narrative_role, etc.)
            // ============================================================
            $validationAfter = $validationBefore;
            $autofixRegression = false;

            // AutoFix ALWAYS runs to ensure Stage 11-17 attributes are set
            $fixedLayout = JTB_AI_AutoFix::applyFixes($layout, $layout['_quality']);

            $fixCount = $fixedLayout['_autofix_count'] ?? 0;
            if ($fixCount > 0) {
                $layout = $fixedLayout;
                $layout['_quality']['autofix_applied'] = true;
                $layout['_quality']['autofix_rules'] = JTB_AI_AutoFix::getAppliedFixes();

                // Re-validate after auto-fix
                $patternsForValidation = self::extractPatternsForValidation($layout);
                $validationAfter = self::validateLayout(['patterns' => $patternsForValidation]);

                // ============================================================
                // REGRESSION CHECK: Did autofix introduce DARK_MISUSE?
                // If DARK_MISUSE appears AFTER autofix but wasn't present BEFORE,
                // treat this as a critical regression => immediate FAIL
                // ============================================================
                $hadDarkMisuseBefore = self::hasViolationType($validationBefore['violations'], 'DARK_MISUSE');
                $hasDarkMisuseAfter = self::hasViolationType($validationAfter['violations'], 'DARK_MISUSE');

                if (!$hadDarkMisuseBefore && $hasDarkMisuseAfter) {
                    // AUTOFIX REGRESSION: AutoFix introduced DARK_MISUSE
                    $autofixRegression = true;
                    $layout['_quality']['autofix_regression'] = true;
                    $layout['_quality']['autofix_regression_type'] = 'DARK_MISUSE';
                    error_log('JTB AI AutoFix REGRESSION: DARK_MISUSE introduced by autofix at attempt ' . $attempts);
                }

                // Update quality with post-fix scores
                $layout['_quality']['score_before_fix'] = $validationBefore['score'];
                $layout['_quality']['score'] = $validationAfter['score'];
                $layout['_quality']['status'] = $validationAfter['status'];
                $layout['_quality']['violations'] = $validationAfter['violations'];
                $layout['_quality']['warnings'] = $validationAfter['warnings'];
            }

            // ============================================================
            // IMMEDIATE FAIL on AutoFix Regression
            // ============================================================
            if ($autofixRegression) {
                $layout['_quality']['failed'] = true;
                $layout['_quality']['decision'] = 'FAIL';
                $layout['_quality']['stop_reason'] = 'AUTOFIX_REGRESSION';
                $layout['_quality']['confidence'] = 0;

                self::logQualityDecision($layout['_quality']);

                // Return best result from previous attempts if available
                if ($bestResult !== null && $bestResult !== $layout) {
                    $bestResult['_quality']['forced_accept'] = !JTB_AI_Confidence::hasCriticalViolations($bestResult['_quality']['violations'] ?? []);
                    $bestResult['_quality']['original_decision'] = 'FAIL';
                    $bestResult['_quality']['stop_reason'] = 'AUTOFIX_REGRESSION_FALLBACK';
                    return $bestResult;
                }

                return $layout;
            }

            // Generate hash after autofix
            $hashAfterFix = JTB_AI_Confidence::generateLayoutHash($layout);
            $layout['_quality']['layout_hash'] = $hashAfterFix;

            // ============================================================
            // CONFIDENCE SCORING + STOP CONDITIONS
            // ============================================================
            $qualityAfter = [
                'score' => $layout['_quality']['score'],
                'violations' => $layout['_quality']['violations'],
                'layout_hash' => $hashAfterFix,
            ];

            $confidenceResult = JTB_AI_Confidence::evaluate(
                $qualityBefore,
                $qualityAfter,
                $attempts,
                $layoutHashHistory
            );

            // Add confidence data to quality
            $layout['_quality']['confidence'] = $confidenceResult['confidence'];
            $layout['_quality']['decision'] = $confidenceResult['decision'];
            $layout['_quality']['stop_reason'] = $confidenceResult['stop_reason'];
            $layout['_quality']['improvement'] = $confidenceResult['improvement'];
            $layout['_quality']['has_critical'] = $confidenceResult['has_critical'];
            $layout['_quality']['is_oscillation'] = $confidenceResult['is_oscillation'];

            // Update hash history for oscillation detection
            $layoutHashHistory[] = $hashAfterFix;

            // Store previous quality for next iteration
            $previousQuality = $layout['_quality'];

            // Track best result
            if ($layout['_quality']['score'] > $bestScore) {
                $bestScore = $layout['_quality']['score'];
                $bestResult = $layout;
            }

            // ============================================================
            // DECISION HANDLING
            // ============================================================
            switch ($confidenceResult['decision']) {
                case 'ACCEPT':
                    // Check if forced accept (max attempts, no critical, but low score)
                    if ($attempts >= $maxAttempts && $layout['_quality']['score'] < 11) {
                        $layout['_quality']['forced_accept'] = true;
                    }
                    self::logQualityDecision($layout['_quality']);
                    return $layout;

                case 'FAIL':
                    // Immediate failure - don't retry
                    $layout['_quality']['failed'] = true;
                    self::logQualityDecision($layout['_quality']);

                    // Return best result if available, otherwise current
                    if ($bestResult !== null && $bestResult !== $layout) {
                        $bestResult['_quality']['forced_accept'] = !$confidenceResult['has_critical'];
                        $bestResult['_quality']['original_decision'] = 'FAIL';
                        $bestResult['_quality']['stop_reason'] = $confidenceResult['stop_reason'];
                        self::logQualityDecision($bestResult['_quality']);
                        return $bestResult;
                    }
                    return $layout;

                case 'RETRY':
                default:
                    // Continue to next attempt
                    continue 2;
            }
        }

        // Max attempts exhausted - return best result
        if ($bestResult !== null) {
            $hasCritical = JTB_AI_Confidence::hasCriticalViolations($bestResult['_quality']['violations'] ?? []);

            if ($hasCritical) {
                // FAIL if critical violations still exist
                $bestResult['_quality']['forced_accept'] = false;
                $bestResult['_quality']['failed'] = true;
                $bestResult['_quality']['decision'] = 'FAIL';
                $bestResult['_quality']['stop_reason'] = 'MAX_ATTEMPTS_WITH_CRITICAL';
            } else {
                // Forced accept if no critical
                $bestResult['_quality']['forced_accept'] = true;
                $bestResult['_quality']['decision'] = 'ACCEPT';
                $bestResult['_quality']['stop_reason'] = 'MAX_ATTEMPTS_NO_CRITICAL';
            }

            self::logQualityDecision($bestResult['_quality']);
            return $bestResult;
        }

        // Complete failure
        $failedQuality = [
            'score' => 0,
            'status' => 'REJECT',
            'breakdown' => [],
            'violations' => ['GENERATION_FAILED: All attempts failed'],
            'warnings' => [],
            'meta' => [],
            'attempt' => $maxAttempts,
            'forced_accept' => false,
            'failed' => true,
            'autofix_applied' => false,
            'confidence' => 0,
            'decision' => 'FAIL',
            'stop_reason' => 'ALL_ATTEMPTS_FAILED',
        ];

        self::logQualityDecision($failedQuality);

        return [
            'ok' => false,
            'error' => 'Failed to generate valid layout after ' . $maxAttempts . ' attempts',
            'sections' => [],
            '_quality' => $failedQuality,
        ];
    }

    /**
     * Extract patterns structure for validation from composed layout
     */
    private static function extractPatternsForValidation(array $layout): array
    {
        $sections = $layout['sections'] ?? [];
        $patternsUsed = $layout['patterns_used'] ?? [];
        $context = $layout['context'] ?? [];

        $patterns = [];
        foreach ($sections as $index => $section) {
            // Get pattern name from multiple sources
            $patternName = $section['_pattern']
                ?? $section['attrs']['_pattern']
                ?? $patternsUsed[$index]
                ?? 'unknown';

            $rows = [];
            foreach ($section['children'] ?? [] as $row) {
                $rowData = [
                    'children' => [],
                ];
                foreach ($row['children'] ?? [] as $column) {
                    $colData = [
                        'children' => $column['children'] ?? [],
                    ];
                    $rowData['children'][] = $colData;
                }
                $rows[] = $rowData;
            }

            // Pass ALL section attributes for validation (Stages 11-17)
            $sectionAttrs = $section['attrs'] ?? [];

            // Also include root-level autofix attributes
            $sectionAttrs['visual_context'] = $section['_visual_context'] ?? $sectionAttrs['visual_context'] ?? 'LIGHT';

            // CRITICAL: If Stage 11-17 attributes are not set yet, compute them from pattern
            // This ensures validation works correctly even BEFORE AutoFix runs
            $sectionAttrs['visual_intent'] = $section['_visual_intent']
                ?? $sectionAttrs['visual_intent']
                ?? JTB_AI_AutoFix::getVisualIntentForPattern($patternName);

            $sectionAttrs['visual_density'] = $section['_visual_density']
                ?? $sectionAttrs['visual_density']
                ?? JTB_AI_AutoFix::getVisualDensityForPattern($patternName);

            $sectionAttrs['visual_scale'] = $section['_visual_scale'] ?? $sectionAttrs['visual_scale'] ?? null;
            $sectionAttrs['typography_scale'] = $section['_typography_scale'] ?? $sectionAttrs['typography_scale'] ?? null;
            $sectionAttrs['text_emphasis'] = $section['_text_emphasis'] ?? $sectionAttrs['text_emphasis'] ?? null;

            // Emotional flow from pattern
            $emotional = JTB_AI_AutoFix::getEmotionalForPattern($patternName);
            $sectionAttrs['emotional_tone'] = $section['_emotional_tone']
                ?? $sectionAttrs['emotional_tone']
                ?? $emotional['tone'];
            $sectionAttrs['attention_level'] = $section['_attention_level']
                ?? $sectionAttrs['attention_level']
                ?? $emotional['attention'];

            // CRITICAL: Narrative role from pattern (for validation BEFORE AutoFix)
            $sectionAttrs['narrative_role'] = $section['_narrative_role']
                ?? $sectionAttrs['narrative_role']
                ?? JTB_AI_AutoFix::getNarrativeRoleForPattern($patternName);

            $sectionAttrs['_pattern'] = $patternName;

            $patterns[] = [
                'pattern' => $patternName,
                'attrs' => $sectionAttrs,
                'rows' => $rows,
            ];
        }

        return $patterns;
    }

    /**
     * Check if violations contain critical issues that must trigger retry
     */
    private static function hasCriticalViolations(array $violations): bool
    {
        $criticalPatterns = [
            'MISSING_FINAL_CTA',
            'FINAL_CTA_NOT_LAST',
            'PRIMARY_COUNT_INVALID',
            'PRIMARY_MISUSE',
            'DARK_MISUSE', // Added in Stage 11
        ];

        foreach ($violations as $violation) {
            foreach ($criticalPatterns as $pattern) {
                if (str_contains($violation, $pattern)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Check if violations array contains a specific violation type
     * Used for regression detection
     *
     * @param array $violations Array of violation strings
     * @param string $type Violation type to check (e.g., 'DARK_MISUSE')
     * @return bool True if violation type is present
     */
    private static function hasViolationType(array $violations, string $type): bool
    {
        foreach ($violations as $violation) {
            if (str_contains($violation, $type)) {
                return true;
            }
        }
        return false;
    }

    // ========================================
    // QUALITY TELEMETRY — Stage 4
    // Purpose: Observability, tuning, analytics, future ML feedback
    // ========================================

    /**
     * Log quality decision to JSONL file for telemetry
     * Called once per generateWithValidation() after final layout selection
     * @param array $quality Quality data from _quality field
     */
    private static function logQualityDecision(array $quality): void
    {
        $logEntry = [
            'timestamp' => date('c'),
            'attempt' => $quality['attempt'] ?? 0,
            'score' => $quality['score'] ?? 0,
            'score_before_fix' => $quality['score_before_fix'] ?? null,
            'status' => $quality['status'] ?? 'UNKNOWN',
            'forced_accept' => $quality['forced_accept'] ?? false,
            'failed' => $quality['failed'] ?? false,
            // Confidence scoring data
            'confidence' => $quality['confidence'] ?? null,
            'decision' => $quality['decision'] ?? null,
            'stop_reason' => $quality['stop_reason'] ?? null,
            'improvement' => $quality['improvement'] ?? null,
            'has_critical' => $quality['has_critical'] ?? null,
            'is_oscillation' => $quality['is_oscillation'] ?? false,
            // AutoFix data
            'autofix_applied' => $quality['autofix_applied'] ?? false,
            'autofix_rules' => isset($quality['autofix_rules']) ? array_column($quality['autofix_rules'], 'rule') : [],
            // Violations and warnings
            'violations' => $quality['violations'] ?? [],
            'warnings' => $quality['warnings'] ?? [],
            'breakdown' => $quality['breakdown'] ?? [],
            'meta' => $quality['meta'] ?? [],
        ];

        $logDir = defined('CMS_ROOT') ? CMS_ROOT . '/logs' : __DIR__ . '/../../logs';

        if (!is_dir($logDir)) {
            @mkdir($logDir, 0755, true);
        }

        $logFile = $logDir . '/ai-quality.log';
        $jsonLine = json_encode($logEntry, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n";

        @file_put_contents($logFile, $jsonLine, FILE_APPEND | LOCK_EX);
    }

    /**
     * Helper to get pattern name from a pattern array
     * Handles both 'pattern' and '_pattern' keys (legacy vs new structure)
     */
    private static function getPatternName(array $pattern): string
    {
        return $pattern['_pattern'] ?? $pattern['pattern'] ?? 'unknown';
    }

    /**
     * Generate layout using the compositional system
     * This is the preferred method for creating unique, modern layouts
     *
     * @param string $prompt User's description
     * @param array $options Generation options
     * @return array Generated layout
     */
    public static function generateComposedLayout(string $prompt, array $options = []): array
    {
        // Parse intent
        $intent = self::parseSemanticIntent($prompt);

        // Build context
        $context = [
            'style' => $intent['style'] ?: ($options['style'] ?? 'modern'),
            'industry' => $intent['industry'] ?: ($options['industry'] ?? 'technology'),
            'tone' => $intent['tone'] ?: ($options['tone'] ?? 'professional'),
            'page_type' => $intent['page_type'] ?: ($options['page_type'] ?? 'landing'),
        ];

        // Inject quality feedback into context for retry attempts
        if (!empty($options['_quality_feedback'])) {
            $context['_quality_feedback'] = $options['_quality_feedback'];
        }

        // Determine composition intent
        $compositionIntent = self::mapToCompositionIntent($intent, $options);

        // Use Composer to get pattern sequence (with quality feedback if available)
        $patterns = JTB_AI_Composer::composePage($compositionIntent, $context);

        // Validate pattern sequence
        $issues = JTB_AI_Composer::validateSequence($patterns);
        if (!empty($issues)) {
            // Log issues but continue - they're warnings, not errors
            error_log('JTB Composer warnings: ' . json_encode($issues));
        }

        // Render patterns to JTB structure
        $sections = JTB_AI_Pattern_Renderer::renderPage($patterns, $context);

        // Validate output
        if (empty($sections)) {
            return [
                'ok' => false,
                'error' => 'Composition produced no sections',
                'sections' => [],
            ];
        }

        return [
            'ok' => true,
            'sections' => $sections,
            'intent' => $intent,
            'composition_intent' => $compositionIntent,
            'patterns_used' => array_map(fn($p) => $p['_pattern'] ?? $p['pattern'] ?? 'unknown', $patterns),
            'context' => $context,
        ];
    }

    /**
     * Map semantic intent to composition intent
     */
    private static function mapToCompositionIntent(array $intent, array $options): string
    {
        $pageType = $intent['page_type'] ?? 'landing';
        $industry = $intent['industry'] ?? 'technology';

        // Check for explicit composition intent in options
        if (!empty($options['composition_intent'])) {
            return $options['composition_intent'];
        }

        // Map page types to composition intents
        return match($pageType) {
            'landing' => $industry === 'saas' || $industry === 'technology' ? 'saas_landing' : 'product_launch',
            'homepage' => 'saas_landing',
            'about' => 'brand_story',
            'services' => 'service_showcase',
            'portfolio' => 'portfolio',
            'contact' => 'service_showcase', // Uses contact_gateway at end
            'pricing' => 'saas_landing',
            default => 'product_launch',
        };
    }

    /**
     * Generate a single pattern (for API use)
     *
     * @param string $patternName Pattern name
     * @param string $variant Pattern variant
     * @param array $context Context data
     * @return array Section structure
     */
    public static function generatePattern(string $patternName, string $variant = 'default', array $context = []): array
    {
        // Get pattern from Composer
        $pattern = JTB_AI_Composer::composePattern($patternName, $variant, $context);

        if (empty($pattern)) {
            return [
                'ok' => false,
                'error' => "Unknown pattern: $patternName",
            ];
        }

        // Render to JTB structure
        $section = JTB_AI_Pattern_Renderer::renderPattern($pattern, $context);

        return [
            'ok' => true,
            'section' => $section,
            'pattern' => $patternName,
            'variant' => $variant,
        ];
    }

    /**
     * Get available patterns and variants
     */
    public static function getAvailablePatterns(): array
    {
        return JTB_AI_Composer::getAvailablePatterns();
    }

    /**
     * Get pattern info
     */
    public static function getPatternInfo(string $patternName): array
    {
        return JTB_AI_Composer::getPatternInfo($patternName);
    }

    // ========================================
    // Semantic Parsing Methods
    // ========================================

    /**
     * Parse user prompt to understand intent
     * @param string $prompt User's prompt
     * @return array Parsed intent
     */
    public static function parseSemanticIntent(string $prompt): array
    {
        $prompt = strtolower(trim($prompt));

        return [
            'page_type' => self::detectPageType($prompt),
            'industry' => self::detectIndustry($prompt),
            'sections' => self::detectSections($prompt),
            'style' => self::detectStyle($prompt),
            'tone' => self::detectTone($prompt),
            'features' => self::detectFeatures($prompt),
            'color_hints' => self::detectColorHints($prompt),
            'layout_hints' => self::detectLayoutHints($prompt)
        ];
    }

    /**
     * Detect page type from prompt
     * @param string $prompt User prompt
     * @return string Page type
     */
    public static function detectPageType(string $prompt): string
    {
        $patterns = [
            'landing' => ['landing page', 'landing', 'sales page', 'conversion page', 'lead gen', 'squeeze page'],
            'homepage' => ['homepage', 'home page', 'main page', 'front page', 'start page'],
            'about' => ['about page', 'about us', 'our story', 'who we are', 'company page', 'team page'],
            'services' => ['services page', 'what we do', 'our services', 'offerings'],
            'contact' => ['contact page', 'contact us', 'get in touch', 'reach us'],
            'pricing' => ['pricing page', 'pricing', 'plans', 'subscription', 'packages'],
            'portfolio' => ['portfolio', 'our work', 'projects', 'case studies', 'showcase'],
            'blog' => ['blog page', 'blog', 'articles', 'news', 'posts'],
            'product' => ['product page', 'product', 'shop page', 'ecommerce'],
            'faq' => ['faq page', 'faq', 'frequently asked', 'help page', 'questions']
        ];

        foreach ($patterns as $type => $keywords) {
            foreach ($keywords as $keyword) {
                if (strpos($prompt, $keyword) !== false) {
                    return $type;
                }
            }
        }

        // Default based on general content
        if (strpos($prompt, 'convert') !== false || strpos($prompt, 'sell') !== false) {
            return 'landing';
        }

        return 'homepage';
    }

    /**
     * Detect industry from prompt
     * @param string $prompt User prompt
     * @return string Industry
     */
    public static function detectIndustry(string $prompt): string
    {
        $industries = [
            'technology' => ['tech', 'software', 'saas', 'app', 'startup', 'digital', 'it ', 'cloud', 'ai', 'platform'],
            'ecommerce' => ['ecommerce', 'e-commerce', 'online store', 'shop', 'products', 'retail', 'marketplace'],
            'healthcare' => ['health', 'medical', 'clinic', 'doctor', 'hospital', 'dental', 'wellness', 'fitness', 'gym'],
            'education' => ['education', 'school', 'university', 'course', 'training', 'learning', 'tutorial', 'academy'],
            'agency' => ['agency', 'creative', 'design studio', 'marketing agency', 'digital agency', 'consultancy'],
            'restaurant' => ['restaurant', 'cafe', 'food', 'menu', 'dining', 'catering', 'bar', 'bistro'],
            'realestate' => ['real estate', 'property', 'realtor', 'homes', 'apartments', 'housing', 'broker'],
            'legal' => ['law', 'legal', 'attorney', 'lawyer', 'law firm', 'counsel'],
            'finance' => ['finance', 'banking', 'investment', 'insurance', 'financial', 'accounting', 'tax'],
            'nonprofit' => ['nonprofit', 'charity', 'foundation', 'ngo', 'donation', 'cause'],
            'travel' => ['travel', 'tourism', 'hotel', 'vacation', 'booking', 'flights', 'adventure'],
            'photography' => ['photography', 'photographer', 'photo studio', 'wedding photography'],
            'construction' => ['construction', 'contractor', 'building', 'renovation', 'architect'],
            'automotive' => ['automotive', 'car', 'auto', 'vehicle', 'dealership', 'garage']
        ];

        foreach ($industries as $industry => $keywords) {
            foreach ($keywords as $keyword) {
                if (strpos($prompt, $keyword) !== false) {
                    return $industry;
                }
            }
        }

        return '';
    }

    /**
     * Detect requested sections from prompt
     * @param string $prompt User prompt
     * @return array Section types
     */
    public static function detectSections(string $prompt): array
    {
        $sections = [];

        $sectionKeywords = [
            // Basic sections
            'hero' => ['hero', 'header', 'banner', 'above the fold', 'main banner'],
            'fullwidth_hero' => ['fullwidth hero', 'full width hero', 'big hero', 'large header'],
            'features' => ['features', 'benefits', 'what we offer', 'key points', 'highlights'],
            'testimonials' => ['testimonials', 'reviews', 'feedback', 'what clients say', 'social proof'],
            'pricing' => ['pricing', 'plans', 'packages', 'cost', 'subscription'],
            'cta' => ['call to action', 'cta', 'sign up section', 'conversion'],
            'faq' => ['faq', 'questions', 'q&a', 'help section', 'frequently asked'],
            'contact' => ['contact', 'get in touch', 'form', 'reach out'],
            'about' => ['about', 'story', 'mission', 'who we are'],
            'team' => ['team', 'staff', 'people', 'members', 'employees'],
            'portfolio' => ['portfolio', 'work', 'projects', 'showcase', 'case studies'],
            'blog' => ['blog', 'articles', 'posts', 'news', 'updates'],
            'stats' => ['stats', 'numbers', 'counters', 'metrics', 'achievements'],
            'services' => ['services', 'offerings', 'what we do'],
            'partners' => ['partners', 'clients', 'logos', 'trusted by'],
            'newsletter' => ['newsletter', 'subscribe', 'email signup', 'mailing list'],
            'gallery' => ['gallery', 'photos', 'images', 'screenshots'],

            // Extended sections
            'architecture' => ['architecture', 'system', 'structure', 'how it works', 'diagram'],
            'tabs_content' => ['tabs', 'tabbed content', 'capabilities', 'features tabs'],
            'circle_counters' => ['performance', 'scores', 'circle counter', 'percentage circles', 'metrics circles'],
            'bar_counters' => ['progress', 'progress bars', 'completion', 'development status'],
            'slider_screenshots' => ['slider', 'carousel', 'slideshow', 'screenshots slider'],
            'video_demo' => ['video', 'demo', 'video demo', 'watch', 'tutorial'],
            'countdown' => ['countdown', 'timer', 'launch', 'coming soon', 'release date'],
            'map_contact' => ['map', 'location', 'address', 'find us', 'visit us'],
            'divider' => ['divider', 'separator', 'line'],
            'signup' => ['signup', 'register', 'create account', 'join']
        ];

        foreach ($sectionKeywords as $section => $keywords) {
            foreach ($keywords as $keyword) {
                if (strpos($prompt, $keyword) !== false) {
                    if (!in_array($section, $sections)) {
                        $sections[] = $section;
                    }
                    break;
                }
            }
        }

        return $sections;
    }

    /**
     * Detect style preferences from prompt
     * @param string $prompt User prompt
     * @return string Style
     */
    public static function detectStyle(string $prompt): string
    {
        $styles = [
            'modern' => ['modern', 'contemporary', 'sleek', 'cutting-edge', 'trendy'],
            'minimal' => ['minimal', 'minimalist', 'clean', 'simple', 'less is more'],
            'bold' => ['bold', 'striking', 'dramatic', 'impactful', 'attention-grabbing'],
            'elegant' => ['elegant', 'sophisticated', 'luxurious', 'premium', 'classy'],
            'playful' => ['playful', 'fun', 'colorful', 'creative', 'vibrant'],
            'corporate' => ['corporate', 'professional', 'business', 'formal', 'enterprise'],
            'creative' => ['creative', 'artistic', 'unique', 'innovative', 'original'],
            'vintage' => ['vintage', 'retro', 'classic', 'nostalgic', 'traditional']
        ];

        foreach ($styles as $style => $keywords) {
            foreach ($keywords as $keyword) {
                if (strpos($prompt, $keyword) !== false) {
                    return $style;
                }
            }
        }

        return 'modern';
    }

    /**
     * Detect tone from prompt
     * @param string $prompt User prompt
     * @return string Tone
     */
    public static function detectTone(string $prompt): string
    {
        $tones = [
            'professional' => ['professional', 'business', 'formal', 'corporate', 'serious'],
            'friendly' => ['friendly', 'warm', 'welcoming', 'approachable', 'casual'],
            'casual' => ['casual', 'relaxed', 'informal', 'laid-back'],
            'luxury' => ['luxury', 'premium', 'exclusive', 'high-end', 'upscale'],
            'playful' => ['playful', 'fun', 'energetic', 'exciting', 'lively'],
            'trustworthy' => ['trustworthy', 'reliable', 'credible', 'honest', 'authentic'],
            'inspiring' => ['inspiring', 'motivating', 'empowering', 'uplifting'],
            'technical' => ['technical', 'detailed', 'precise', 'informative'],
            'urgent' => ['urgent', 'limited time', 'act now', 'hurry', 'deadline']
        ];

        foreach ($tones as $tone => $keywords) {
            foreach ($keywords as $keyword) {
                if (strpos($prompt, $keyword) !== false) {
                    return $tone;
                }
            }
        }

        return 'professional';
    }

    /**
     * Detect specific features mentioned
     * @param string $prompt User prompt
     * @return array Features
     */
    public static function detectFeatures(string $prompt): array
    {
        $features = [];

        $featureKeywords = [
            'animations' => ['animated', 'animation', 'motion', 'moving'],
            'video_background' => ['video background', 'video header', 'video hero'],
            'parallax' => ['parallax', 'scrolling effect'],
            'dark_mode' => ['dark mode', 'dark theme', 'dark design'],
            'fullwidth' => ['fullwidth', 'full-width', 'edge to edge'],
            'sticky_header' => ['sticky header', 'fixed header'],
            'countdown' => ['countdown', 'timer', 'launch date'],
            'map' => ['map', 'location', 'address'],
            'gallery' => ['gallery', 'photos', 'images'],
            'slider' => ['slider', 'carousel', 'slideshow'],
            'forms' => ['form', 'contact form', 'signup form']
        ];

        foreach ($featureKeywords as $feature => $keywords) {
            foreach ($keywords as $keyword) {
                if (strpos($prompt, $keyword) !== false) {
                    $features[] = $feature;
                    break;
                }
            }
        }

        return $features;
    }

    /**
     * Detect color hints from prompt
     * @param string $prompt User prompt
     * @return array Color hints
     */
    public static function detectColorHints(string $prompt): array
    {
        $colorHints = [];

        $colors = [
            'blue' => ['blue', 'navy', 'azure', 'sapphire'],
            'green' => ['green', 'emerald', 'mint', 'forest'],
            'red' => ['red', 'crimson', 'ruby', 'scarlet'],
            'purple' => ['purple', 'violet', 'lavender', 'plum'],
            'orange' => ['orange', 'amber', 'coral', 'peach'],
            'yellow' => ['yellow', 'gold', 'sunny'],
            'pink' => ['pink', 'rose', 'magenta', 'fuchsia'],
            'black' => ['black', 'dark', 'noir'],
            'white' => ['white', 'light', 'bright'],
            'gray' => ['gray', 'grey', 'neutral']
        ];

        foreach ($colors as $color => $keywords) {
            foreach ($keywords as $keyword) {
                if (strpos($prompt, $keyword) !== false) {
                    $colorHints[] = $color;
                    break;
                }
            }
        }

        return $colorHints;
    }

    /**
     * Detect layout hints from prompt
     * @param string $prompt User prompt
     * @return array Layout hints
     */
    public static function detectLayoutHints(string $prompt): array
    {
        $hints = [];

        if (strpos($prompt, 'single column') !== false || strpos($prompt, 'one column') !== false) {
            $hints['columns'] = 1;
        }
        if (strpos($prompt, 'two column') !== false || strpos($prompt, '2 column') !== false) {
            $hints['columns'] = 2;
        }
        if (strpos($prompt, 'three column') !== false || strpos($prompt, '3 column') !== false) {
            $hints['columns'] = 3;
        }

        if (strpos($prompt, 'long page') !== false || strpos($prompt, 'long-form') !== false) {
            $hints['length'] = 'long';
        }
        if (strpos($prompt, 'short page') !== false || strpos($prompt, 'simple page') !== false) {
            $hints['length'] = 'short';
        }

        if (strpos($prompt, 'sidebar') !== false) {
            $hints['sidebar'] = true;
        }

        return $hints;
    }

    // ========================================
    // Section Mapping Methods
    // ========================================

    /**
     * Map section type to modules
     * @param string $sectionType Section type
     * @return array Section configuration with rows and modules
     */
    public static function mapSectionToModules(string $sectionType): array
    {
        return match($sectionType) {
            // Basic sections
            'hero' => self::mapHeroSection(),
            'fullwidth_hero' => self::mapFullwidthHeroSection(),
            'features' => self::mapFeaturesSection(),
            'testimonials' => self::mapTestimonialsSection(),
            'cta' => self::mapCTASection(),
            'pricing' => self::mapPricingSection(),
            'faq' => self::mapFAQSection(),
            'contact' => self::mapContactSection(),
            'about' => self::mapAboutSection(),
            'team' => self::mapTeamSection(),
            'portfolio' => self::mapPortfolioSection(),
            'blog' => self::mapBlogSection(),
            'stats' => self::mapStatsSection(),
            'services' => self::mapServicesSection(),
            'partners' => self::mapPartnersSection(),
            'newsletter' => self::mapNewsletterSection(),
            'footer' => self::mapFooterSection(),
            'header' => self::mapHeaderSection(),
            'gallery' => self::mapGallerySection(),
            // Extended sections (from spec)
            'architecture' => self::mapArchitectureSection(),
            'tabs_content' => self::mapTabsContentSection(),
            'circle_counters' => self::mapCircleCountersSection(),
            'bar_counters' => self::mapBarCountersSection(),
            'slider_screenshots' => self::mapSliderSection(),
            'video_demo' => self::mapVideoSection(),
            'countdown' => self::mapCountdownSection(),
            'map_contact' => self::mapMapContactSection(),
            'divider' => self::mapDividerSection(),
            'signup' => self::mapSignupSection(),
            default => self::mapGenericSection($sectionType)
        };
    }

    private static function mapHeroSection(): array
    {
        // Hierarchy: content column is primary (wider), image is supporting
        return [
            'type' => 'hero',
            'rows' => [
                [
                    'columns' => '7_12_5_12',
                    'modules' => [
                        ['heading', 'text', 'button'],  // primary - message
                        ['image']                        // supporting - visual
                    ]
                ]
            ]
        ];
    }

    private static function mapFeaturesSection(int $numFeatures = 3): array
    {
        // Hierarchy: first blurb is primary (larger column), others are supporting
        return [
            'type' => 'features',
            'rows' => [
                [
                    'columns' => '1_1',
                    'modules' => [['heading']]
                ],
                [
                    'columns' => '1_2_1_4_1_4',
                    'modules' => [
                        ['blurb'],  // primary - wider column
                        ['blurb'],  // supporting
                        ['blurb']   // supporting
                    ]
                ]
            ]
        ];
    }

    private static function mapTestimonialsSection(int $numTestimonials = 3): array
    {
        // Rhythm: asymmetric layout - one large testimonial + two smaller
        return [
            'type' => 'testimonials',
            'rows' => [
                [
                    'columns' => '1_1',
                    'modules' => [['heading']]
                ],
                [
                    'columns' => '1_2_1_4_1_4',
                    'modules' => [
                        ['testimonial'],
                        ['testimonial'],
                        ['testimonial']
                    ]
                ]
            ]
        ];
    }

    private static function mapCTASection(): array
    {
        // Escalation: CTA is decision moment, not repeat of hero
        // Structure: social proof (trust) + urgency + primary action + secondary path
        return [
            'type' => 'cta',
            'rows' => [
                [
                    'columns' => '1_6_2_3_1_6',
                    'modules' => [
                        [],
                        ['heading'],  // decision headline (not welcome)
                        []
                    ]
                ],
                [
                    'columns' => '1_4_1_2_1_4',
                    'modules' => [
                        [],
                        ['text'],  // urgency/scarcity or summary of value
                        []
                    ]
                ],
                [
                    'columns' => '1_3_1_3_1_3',
                    'modules' => [
                        ['number_counter'],  // social proof: users/customers
                        ['button'],          // primary CTA (stronger than hero)
                        ['number_counter']   // social proof: rating/satisfaction
                    ]
                ]
            ]
        ];
    }

    private static function mapPricingSection(int $numPlans = 3): array
    {
        // Hierarchy: middle plan is featured (wider), side plans are supporting
        return [
            'type' => 'pricing',
            'rows' => [
                [
                    'columns' => '1_4_1_2_1_4',
                    'modules' => [
                        [],
                        ['heading', 'text'],
                        []
                    ]
                ],
                [
                    'columns' => '1_4_5_12_1_4',
                    'modules' => [
                        ['pricing_table'],  // supporting - starter
                        ['pricing_table'],  // featured - recommended (wider)
                        ['pricing_table']   // supporting - enterprise
                    ]
                ]
            ]
        ];
    }

    private static function mapFAQSection(int $numItems = 5): array
    {
        return [
            'type' => 'faq',
            'rows' => [
                [
                    'columns' => '1_1',
                    'modules' => [['heading']]
                ],
                [
                    'columns' => '2_3_1_3',
                    'modules' => [
                        ['accordion'],
                        ['text', 'button']
                    ]
                ]
            ]
        ];
    }

    private static function mapContactSection(): array
    {
        // Rhythm: reversed asymmetric 1/3 + 2/3 - mirrors FAQ layout
        return [
            'type' => 'contact',
            'rows' => [
                [
                    'columns' => '1_1',
                    'modules' => [['heading', 'text']]
                ],
                [
                    'columns' => '1_3_2_3',
                    'modules' => [
                        ['blurb', 'blurb', 'blurb'],
                        ['contact_form']
                    ]
                ]
            ]
        ];
    }

    private static function mapAboutSection(): array
    {
        return [
            'type' => 'about',
            'rows' => [
                [
                    'columns' => '1_2_1_2',
                    'modules' => [
                        ['image'],
                        ['heading', 'text', 'button']
                    ]
                ]
            ]
        ];
    }

    private static function mapTeamSection(int $numMembers = 4): array
    {
        // Hierarchy: first row has leader (CEO/founder), second row has supporting team
        return [
            'type' => 'team',
            'rows' => [
                [
                    'columns' => '1_1',
                    'modules' => [['heading', 'text']]
                ],
                [
                    'columns' => '1_4_1_2_1_4',
                    'modules' => [
                        [],
                        ['team_member'],  // leader - prominent center position
                        []
                    ]
                ],
                [
                    'columns' => '1_3_1_3_1_3',
                    'modules' => [
                        ['team_member'],  // supporting
                        ['team_member'],  // supporting
                        ['team_member']   // supporting
                    ]
                ]
            ]
        ];
    }

    private static function mapPortfolioSection(): array
    {
        return [
            'type' => 'portfolio',
            'rows' => [
                [
                    'columns' => '1_1',
                    'modules' => [['heading']]
                ],
                [
                    'columns' => '1_1',
                    'modules' => [['portfolio']]
                ]
            ]
        ];
    }

    private static function mapBlogSection(): array
    {
        return [
            'type' => 'blog',
            'rows' => [
                [
                    'columns' => '1_1',
                    'modules' => [['heading']]
                ],
                [
                    'columns' => '1_1',
                    'modules' => [['blog']]
                ]
            ]
        ];
    }

    private static function mapStatsSection(): array
    {
        // Hierarchy: first stat is hero metric (wider), others supporting
        return [
            'type' => 'stats',
            'rows' => [
                [
                    'columns' => '5_12_1_6_1_6_1_6',
                    'modules' => [
                        ['number_counter'],  // hero metric - key number
                        ['number_counter'],  // supporting
                        ['number_counter'],  // supporting
                        ['number_counter']   // supporting
                    ],
                    'attrs' => ['compact' => true]
                ]
            ]
        ];
    }

    private static function mapServicesSection(): array
    {
        // Rhythm: 2-column wide layout - contrast with 3-column features
        return [
            'type' => 'services',
            'rows' => [
                [
                    'columns' => '1_1',
                    'modules' => [['heading', 'text']]
                ],
                [
                    'columns' => '1_2_1_2',
                    'modules' => [
                        ['blurb'],
                        ['blurb']
                    ]
                ],
                [
                    'columns' => '1_3_1_3_1_3',
                    'modules' => [
                        ['blurb'],
                        ['blurb'],
                        ['blurb']
                    ]
                ]
            ]
        ];
    }

    private static function mapPartnersSection(): array
    {
        return [
            'type' => 'partners',
            'rows' => [
                [
                    'columns' => '1_1',
                    'modules' => [['heading']]
                ],
                [
                    'columns' => '1_6_1_6_1_6_1_6_1_6_1_6',
                    'modules' => [
                        ['image'],
                        ['image'],
                        ['image'],
                        ['image'],
                        ['image'],
                        ['image']
                    ]
                ]
            ]
        ];
    }

    private static function mapNewsletterSection(): array
    {
        return [
            'type' => 'newsletter',
            'rows' => [
                [
                    'columns' => '1_4_1_2_1_4',
                    'modules' => [
                        [],
                        ['heading', 'text', 'contact_form'],
                        []
                    ]
                ]
            ]
        ];
    }

    private static function mapFooterSection(): array
    {
        return [
            'type' => 'footer',
            'rows' => [
                [
                    'columns' => '1_4_1_4_1_4_1_4',
                    'modules' => [
                        ['footer_info'],
                        ['footer_menu'],
                        ['footer_menu'],
                        ['social_icons']
                    ]
                ],
                [
                    'columns' => '1_1',
                    'modules' => [['copyright']]
                ]
            ]
        ];
    }

    private static function mapHeaderSection(): array
    {
        return [
            'type' => 'header',
            'rows' => [
                [
                    'columns' => '1_4_1_2_1_4',
                    'modules' => [
                        ['site_logo'],
                        ['menu'],
                        ['header_button']
                    ]
                ]
            ]
        ];
    }

    private static function mapGallerySection(): array
    {
        return [
            'type' => 'gallery',
            'rows' => [
                [
                    'columns' => '1_1',
                    'modules' => [['heading']]
                ],
                [
                    'columns' => '1_1',
                    'modules' => [['gallery']]
                ]
            ]
        ];
    }

    // ========================================
    // Extended Section Mappings (from spec)
    // ========================================

    private static function mapFullwidthHeroSection(): array
    {
        return [
            'type' => 'fullwidth_hero',
            'rows' => [
                [
                    'columns' => '1_1',
                    'modules' => [['fullwidth_header']]
                ]
            ]
        ];
    }

    private static function mapArchitectureSection(): array
    {
        return [
            'type' => 'architecture',
            'rows' => [
                [
                    'columns' => '1_1',
                    'modules' => [['heading', 'text']]
                ],
                [
                    'columns' => '1_2_1_2',
                    'modules' => [
                        ['accordion'],
                        ['image']
                    ]
                ]
            ]
        ];
    }

    private static function mapTabsContentSection(): array
    {
        return [
            'type' => 'tabs_content',
            'rows' => [
                [
                    'columns' => '1_1',
                    'modules' => [['heading']]
                ],
                [
                    'columns' => '1_1',
                    'modules' => [['tabs']]
                ]
            ]
        ];
    }

    private static function mapCircleCountersSection(): array
    {
        return [
            'type' => 'circle_counters',
            'rows' => [
                [
                    'columns' => '1_1',
                    'modules' => [['heading']]
                ],
                [
                    'columns' => '1_4_1_4_1_4_1_4',
                    'modules' => [
                        ['circle_counter'],
                        ['circle_counter'],
                        ['circle_counter'],
                        ['circle_counter']
                    ]
                ]
            ]
        ];
    }

    private static function mapBarCountersSection(): array
    {
        return [
            'type' => 'bar_counters',
            'rows' => [
                [
                    'columns' => '1_3_2_3',
                    'modules' => [
                        ['heading', 'text'],
                        ['bar_counter']
                    ]
                ]
            ]
        ];
    }

    private static function mapSliderSection(): array
    {
        return [
            'type' => 'slider_screenshots',
            'rows' => [
                [
                    'columns' => '1_1',
                    'modules' => [['heading']]
                ],
                [
                    'columns' => '1_1',
                    'modules' => [['slider']]
                ]
            ]
        ];
    }

    private static function mapVideoSection(): array
    {
        return [
            'type' => 'video_demo',
            'rows' => [
                [
                    'columns' => '1_1',
                    'modules' => [['heading']]
                ],
                [
                    'columns' => '1_1',
                    'modules' => [['video']]
                ]
            ]
        ];
    }

    private static function mapCountdownSection(): array
    {
        return [
            'type' => 'countdown',
            'rows' => [
                [
                    'columns' => '1_1',
                    'modules' => [['heading', 'countdown', 'button']]
                ]
            ]
        ];
    }

    private static function mapMapContactSection(): array
    {
        return [
            'type' => 'map_contact',
            'rows' => [
                [
                    'columns' => '2_3_1_3',
                    'modules' => [
                        ['map'],
                        ['heading', 'blurb', 'blurb', 'blurb']
                    ]
                ]
            ]
        ];
    }

    private static function mapDividerSection(): array
    {
        return [
            'type' => 'divider',
            'rows' => [
                [
                    'columns' => '1_1',
                    'modules' => [['divider']]
                ]
            ]
        ];
    }

    private static function mapSignupSection(): array
    {
        return [
            'type' => 'signup',
            'rows' => [
                [
                    'columns' => '1_4_1_2_1_4',
                    'modules' => [
                        [],
                        ['heading', 'text', 'signup'],
                        []
                    ]
                ]
            ]
        ];
    }

    private static function mapGenericSection(string $type): array
    {
        return [
            'type' => $type,
            'rows' => [
                [
                    'columns' => '1_1',
                    'modules' => [['heading', 'text']]
                ]
            ]
        ];
    }

    // ========================================
    // Assembly Methods
    // ========================================

    /**
     * Assemble complete layout from sections
     * @param array $sections Array of section structures
     * @return array Complete layout
     */
    public static function assembleLayout(array $sections): array
    {
        return [
            'version' => '1.0',
            'sections' => $sections
        ];
    }

    /**
     * Assemble section structure
     * @param array $rows Row structures
     * @param array $attrs Section attributes
     * @return array Section structure
     */
    public static function assembleSection(array $rows, array $attrs = []): array
    {
        return [
            'id' => self::generateId('section'),
            'type' => 'section',
            'attrs' => $attrs,
            'children' => $rows
        ];
    }

    /**
     * Assemble row structure
     * @param array $columns Column structures
     * @param array $attrs Row attributes
     * @return array Row structure
     */
    public static function assembleRow(array $columns, array $attrs = []): array
    {
        return [
            'id' => self::generateId('row'),
            'type' => 'row',
            'attrs' => $attrs,
            'children' => $columns
        ];
    }

    /**
     * Assemble column structure
     * @param array $modules Module structures
     * @param array $attrs Column attributes
     * @return array Column structure
     */
    public static function assembleColumn(array $modules, array $attrs = []): array
    {
        return [
            'id' => self::generateId('column'),
            'type' => 'column',
            'attrs' => $attrs,
            'children' => $modules
        ];
    }

    /**
     * Assemble module structure
     * @param string $type Module type
     * @param array $attrs Module attributes
     * @return array Module structure
     */
    public static function assembleModule(string $type, array $attrs): array
    {
        return [
            'id' => self::generateId($type),
            'type' => $type,
            'attrs' => $attrs,
            'children' => []
        ];
    }

    // ========================================
    // Branding Methods
    // ========================================

    /**
     * Apply branding colors to layout
     * @param array $layout Layout structure
     * @param array $branding Branding colors
     * @return array Layout with branding applied
     */
    public static function applyBranding(array $layout, array $branding): array
    {
        $primary = $branding['primary'] ?? $branding['primary_color'] ?? '#3B82F6';
        $secondary = $branding['secondary'] ?? $branding['secondary_color'] ?? '#1E40AF';
        $accent = $branding['accent'] ?? $branding['accent_color'] ?? '#F59E0B';

        // Walk through layout and apply colors
        array_walk_recursive($layout, function(&$value, $key) use ($primary, $secondary, $accent) {
            if (is_string($value)) {
                // Replace placeholder colors
                $value = str_replace(['{{primary}}', '{{secondary}}', '{{accent}}'], [$primary, $secondary, $accent], $value);
            }

            // Apply to specific attributes
            if ($key === 'button_background' || $key === 'button_bg_color') {
                if ($value === '' || $value === null) {
                    $value = $primary;
                }
            }
            if ($key === 'button_hover_background' || $key === 'button_hover_bg') {
                if ($value === '' || $value === null) {
                    $value = $secondary;
                }
            }
            if ($key === 'accent_color') {
                if ($value === '' || $value === null) {
                    $value = $accent;
                }
            }
        });

        return $layout;
    }

    // ========================================
    // Validation Methods
    // ========================================

    /**
     * Validate generated layout
     * @param array $layout Layout structure
     * @return bool Whether layout is valid
     */
    public static function validateOutput(array $layout): bool
    {
        if (empty($layout['sections'])) {
            return false;
        }

        foreach ($layout['sections'] as $section) {
            if (!self::validateSection($section)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Validate section structure
     * @param array $section Section structure
     * @return bool Whether section is valid
     */
    private static function validateSection(array $section): bool
    {
        if (empty($section['id']) || empty($section['type']) || $section['type'] !== 'section') {
            return false;
        }

        if (!isset($section['children']) || !is_array($section['children'])) {
            return false;
        }

        foreach ($section['children'] as $row) {
            if (!self::validateRow($row)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Validate row structure
     * @param array $row Row structure
     * @return bool Whether row is valid
     */
    private static function validateRow(array $row): bool
    {
        if (empty($row['id']) || empty($row['type']) || $row['type'] !== 'row') {
            return false;
        }

        if (!isset($row['children']) || !is_array($row['children'])) {
            return false;
        }

        foreach ($row['children'] as $column) {
            if (!self::validateColumn($column)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Validate column structure
     * @param array $column Column structure
     * @return bool Whether column is valid
     */
    private static function validateColumn(array $column): bool
    {
        if (empty($column['id']) || empty($column['type']) || $column['type'] !== 'column') {
            return false;
        }

        if (!isset($column['children']) || !is_array($column['children'])) {
            return false;
        }

        return true;
    }

    /**
     * Fix common issues in generated layouts
     * @param array $layout Layout structure
     * @return array Fixed layout
     */
    public static function fixCommonIssues(array $layout): array
    {
        // Ensure all elements have IDs
        self::ensureIds($layout);

        // Fix missing attrs
        if (!isset($layout['sections'])) {
            $layout = ['sections' => $layout];
        }

        foreach ($layout['sections'] as &$section) {
            if (!isset($section['attrs'])) {
                $section['attrs'] = [];
            }

            foreach ($section['children'] ?? [] as &$row) {
                if (!isset($row['attrs'])) {
                    $row['attrs'] = [];
                }

                foreach ($row['children'] ?? [] as &$column) {
                    if (!isset($column['attrs'])) {
                        $column['attrs'] = [];
                    }

                    foreach ($column['children'] ?? [] as &$module) {
                        if (!isset($module['attrs'])) {
                            $module['attrs'] = [];
                        }
                        if (!isset($module['children'])) {
                            $module['children'] = [];
                        }
                    }
                }
            }
        }

        return $layout;
    }

    /**
     * Ensure all elements have unique IDs
     * @param array &$layout Layout structure
     */
    private static function ensureIds(array &$layout): void
    {
        if (!isset($layout['sections'])) {
            return;
        }

        foreach ($layout['sections'] as &$section) {
            if (empty($section['id'])) {
                $section['id'] = self::generateId('section');
            }

            foreach ($section['children'] ?? [] as &$row) {
                if (empty($row['id'])) {
                    $row['id'] = self::generateId('row');
                }

                foreach ($row['children'] ?? [] as &$column) {
                    if (empty($column['id'])) {
                        $column['id'] = self::generateId('column');
                    }

                    foreach ($column['children'] ?? [] as &$module) {
                        if (empty($module['id'])) {
                            $module['id'] = self::generateId($module['type'] ?? 'module');
                        }
                    }
                }
            }
        }
    }

    // ========================================
    // Utility Methods
    // ========================================

    /**
     * Generate unique ID
     * @param string $prefix ID prefix
     * @return string Unique ID
     */
    public static function generateId(string $prefix): string
    {
        return $prefix . '_' . bin2hex(random_bytes(8));
    }

    /**
     * Parse column layout string to widths
     * @param string $layout Layout string (e.g., "1_2_1_2")
     * @return array Width values
     */
    private static function parseColumnLayout(string $layout): array
    {
        $parts = explode('_', $layout);
        $widths = [];

        for ($i = 0; $i < count($parts); $i += 2) {
            if (isset($parts[$i]) && isset($parts[$i + 1])) {
                $widths[] = $parts[$i] . '_' . $parts[$i + 1];
            }
        }

        return $widths ?: ['1_1'];
    }

    /**
     * Get default sections for page type
     * @param string $pageType Page type
     * @return array Section types
     */
    private static function getSectionsForPageType(string $pageType): array
    {
        $pageSections = [
            // Landing page - conversion focused
            'landing' => [
                'fullwidth_hero', 'stats', 'features', 'about',
                'testimonials', 'pricing', 'faq', 'cta'
            ],

            // Homepage - comprehensive overview
            'homepage' => [
                'fullwidth_hero', 'stats', 'features', 'about', 'architecture',
                'tabs_content', 'services', 'circle_counters', 'slider_screenshots',
                'video_demo', 'testimonials', 'pricing', 'faq', 'gallery',
                'team', 'map_contact', 'contact', 'countdown', 'blog',
                'portfolio', 'cta', 'divider', 'newsletter'
            ],

            // About page - company focused
            'about' => [
                'fullwidth_hero', 'about', 'stats', 'team',
                'testimonials', 'partners', 'cta'
            ],

            // Services page
            'services' => [
                'fullwidth_hero', 'services', 'features', 'tabs_content',
                'bar_counters', 'testimonials', 'pricing', 'cta'
            ],

            // Contact page
            'contact' => [
                'hero', 'map_contact', 'contact'
            ],

            // Pricing page
            'pricing' => [
                'hero', 'pricing', 'features', 'faq',
                'testimonials', 'cta'
            ],

            // Portfolio page
            'portfolio' => [
                'hero', 'portfolio', 'testimonials', 'cta'
            ],

            // Blog page
            'blog' => ['hero', 'blog'],

            // Product page
            'product' => [
                'fullwidth_hero', 'features', 'tabs_content', 'gallery',
                'video_demo', 'testimonials', 'pricing', 'faq', 'cta'
            ],

            // FAQ page
            'faq' => ['hero', 'faq', 'contact'],

            // Technology/SaaS page (full spec)
            'technology' => [
                'fullwidth_hero', 'stats', 'features', 'architecture',
                'tabs_content', 'services', 'circle_counters', 'bar_counters',
                'slider_screenshots', 'video_demo', 'testimonials', 'pricing',
                'faq', 'gallery', 'team', 'map_contact', 'contact',
                'countdown', 'blog', 'portfolio', 'cta', 'divider', 'newsletter'
            ]
        ];

        return $pageSections[$pageType] ?? $pageSections['homepage'];
    }

    /**
     * Get section-specific attributes
     * @param string $sectionType Section type
     * @param array $context Context data
     * @return array Section attributes
     */
    private static function getSectionAttrs(string $sectionType, array $context = []): array
    {
        $style = $context['style'] ?? 'modern';

        // Use JTB_AI_Styles for professional section styling
        return JTB_AI_Styles::getSectionAttrs($sectionType, $style, $context);
    }

    // ========================================
    // LAYOUT QUALITY SCORING ENGINE
    // ========================================

    /**
     * Validate layout quality and return detailed scoring
     * @param array $layout Layout with patterns array
     * @return array Validation result with score, status, breakdown, violations, warnings, meta
     */
    public static function validateLayout(array $layout): array
    {
        $patterns = $layout['patterns'] ?? [];

        $meta = self::computeLayoutMeta($patterns);
        $violations = self::detectHardViolations($patterns, $meta);
        $warnings = self::detectWarnings($patterns, $meta);

        $breakdown = [
            'rhythm' => self::scoreRhythm($patterns, $meta),
            'hierarchy' => self::scoreHierarchy($patterns, $meta),
            'contrast' => self::scoreContrast($patterns, $meta),
            'content' => self::scoreContent($patterns),
            'conversion' => self::scoreConversion($patterns, $meta),
        ];

        $score = array_sum($breakdown);
        $score = max(0, min(25, $score));

        $status = match(true) {
            $score >= 21 => 'EXCELLENT',
            $score >= 16 => 'GOOD',
            $score >= 11 => 'ACCEPTABLE',
            default => 'REJECT',
        };

        return [
            'score' => $score,
            'status' => $status,
            'breakdown' => $breakdown,
            'violations' => $violations,
            'warnings' => $warnings,
            'meta' => $meta,
            'metrics' => $meta, // Alias for AutoFix compatibility
        ];
    }

    /**
     * Compute layout metadata for scoring
     */
    private static function computeLayoutMeta(array $patterns): array
    {
        $totalSections = count($patterns);
        $darkSections = 0;
        $primarySections = 0;
        $gridSequences = 0;
        $breathingSpaceCount = 0;
        $darkMisuseCount = 0;
        $darkMisusePatterns = [];

        // Stage 12: Visual Intent counters
        $viDominantCount = 0;
        $viEmphasisCount = 0;
        $viNeutralCount = 0;
        $viSoftCount = 0;
        $viDominantPatterns = [];

        $gridPatterns = ['grid_density', 'grid_featured'];
        $prevWasGrid = false;

        foreach ($patterns as $pattern) {
            $ctx = $pattern['attrs']['visual_context'] ?? 'LIGHT';
            $patternName = self::getPatternName($pattern);

            if ($ctx === 'DARK') {
                $darkSections++;

                // Check for DARK_MISUSE (DARK on LIGHT-only pattern)
                if (in_array($patternName, self::LIGHT_ONLY_PATTERNS)) {
                    $darkMisuseCount++;
                    $darkMisusePatterns[] = $patternName;
                } elseif (!in_array($patternName, self::DARK_ALLOWED_PATTERNS) && $patternName !== 'final_cta') {
                    // Unknown pattern with DARK - treat as misuse
                    $darkMisuseCount++;
                    $darkMisusePatterns[] = $patternName . ' (unknown)';
                }
            }
            if ($ctx === 'PRIMARY') {
                $primarySections++;
            }
            if ($patternName === 'breathing_space') {
                $breathingSpaceCount++;
            }

            // Stage 12: Track visual intent distribution
            $visualIntent = $pattern['attrs']['visual_intent'] ?? $pattern['attrs']['_visual_intent'] ?? null;
            if ($visualIntent) {
                switch ($visualIntent) {
                    case 'DOMINANT':
                        $viDominantCount++;
                        $viDominantPatterns[] = $patternName;
                        break;
                    case 'EMPHASIS':
                        $viEmphasisCount++;
                        break;
                    case 'NEUTRAL':
                        $viNeutralCount++;
                        break;
                    case 'SOFT':
                        $viSoftCount++;
                        break;
                }
            }

            $isGrid = in_array($patternName, $gridPatterns);
            if ($isGrid && $prevWasGrid) {
                $gridSequences++;
            }
            $prevWasGrid = $isGrid;
        }

        $vdDenseCount = 0;
        $vdNormalCount = 0;
        $vdSparseCount = 0;
        $denseChainCount = 0;
        $rhythmBreaksCount = 0;
        $spacingOverridesCount = 0;
        $prevDensity = null;
        $denseStreak = 0;

        foreach ($patterns as $pattern) {
            $density = $pattern['attrs']['visual_density'] ?? 'NORMAL';
            $beforeSpacing = $pattern['attrs']['before_spacing'] ?? null;
            $afterSpacing = $pattern['attrs']['after_spacing'] ?? null;

            switch ($density) {
                case 'DENSE':
                    $vdDenseCount++;
                    $denseStreak++;
                    if ($denseStreak > 2) {
                        $denseChainCount++;
                    }
                    break;
                case 'SPARSE':
                    $vdSparseCount++;
                    $denseStreak = 0;
                    break;
                default:
                    $vdNormalCount++;
                    $denseStreak = 0;
            }

            if ($beforeSpacing || $afterSpacing) {
                $spacingOverridesCount++;
            }
            if ($beforeSpacing === 'xl' || $beforeSpacing === '2xl' || $afterSpacing === 'xl' || $afterSpacing === '2xl') {
                $rhythmBreaksCount++;
            }

            $prevDensity = $density;
        }

        $scaleXs = 0;
        $scaleSm = 0;
        $scaleMd = 0;
        $scaleLg = 0;
        $scaleXl = 0;
        $heroScale = null;
        $ctaScale = null;
        $typoXlCount = 0;
        $typoLgCount = 0;
        $typoSoftCount = 0;
        $heroTypography = null;
        $ctaTypography = null;

        foreach ($patterns as $pattern) {
            $scale = $pattern['attrs']['visual_scale'] ?? 'MD';
            $patternName = self::getPatternName($pattern);
            $typoScale = $pattern['attrs']['typography_scale'] ?? 'MD';
            $textEmphasis = $pattern['attrs']['text_emphasis'] ?? 'normal';

            switch (strtoupper($scale)) {
                case 'XS': $scaleXs++; break;
                case 'SM': $scaleSm++; break;
                case 'MD': $scaleMd++; break;
                case 'LG': $scaleLg++; break;
                case 'XL': $scaleXl++; break;
            }

            if (strtoupper($typoScale) === 'XL') $typoXlCount++;
            if (strtoupper($typoScale) === 'LG') $typoLgCount++;
            if ($textEmphasis === 'soft') $typoSoftCount++;

            if (strpos($patternName, 'hero') === 0 && $heroScale === null) {
                $heroScale = strtoupper($scale);
                $heroTypography = strtoupper($typoScale);
            }
            if ($patternName === 'final_cta' || $patternName === 'cta') {
                $ctaScale = strtoupper($scale);
                $ctaTypography = strtoupper($typoScale);
            }
        }

        $attHighCount = 0;
        $attMedCount = 0;
        $attLowCount = 0;
        $hasTrustSection = false;
        $hasCalmSection = false;
        $urgencyPosPercent = null;
        $emotionalFlowArr = [];

        foreach ($patterns as $idx => $pattern) {
            $attention = $pattern['attrs']['attention_level'] ?? 'medium';
            $tone = $pattern['attrs']['emotional_tone'] ?? 'focus';

            switch ($attention) {
                case 'high': $attHighCount++; break;
                case 'medium': $attMedCount++; break;
                case 'low': $attLowCount++; break;
            }

            if ($tone === 'trust') $hasTrustSection = true;
            if ($tone === 'calm') $hasCalmSection = true;

            if ($tone === 'urgency' && $urgencyPosPercent === null) {
                $urgencyPosPercent = $totalSections > 0 ? round(($idx / $totalSections) * 100) : 0;
            }

            $emotionalFlowArr[] = strtoupper(substr($tone, 0, 1));
        }

        $emotionalFlow = implode('-', $emotionalFlowArr);

        // Stage 17: Narrative flow metrics
        $narrativeValidation = JTB_AI_AutoFix::getNarrativeValidation($patterns);
        $narrativeSignature = $narrativeValidation['signature'] ?? '';
        $narrativeScore = $narrativeValidation['score'] ?? 0;
        $missingNarrativeRoles = $narrativeValidation['missing_roles'] ?? [];
        $narrativeOutOfOrder = $narrativeValidation['out_of_order_roles'] ?? [];
        $brokenStoryFlow = $narrativeScore < 40 || in_array('CTA_BEFORE_PROMISE', $narrativeValidation['issues'] ?? []);
        $hasHook = $narrativeValidation['has_hook'] ?? false;
        $hasPromise = $narrativeValidation['has_promise'] ?? false;
        $hasProof = $narrativeValidation['has_proof'] ?? false;
        $hasResolution = $narrativeValidation['has_resolution'] ?? false;

        // Stage 18: Narrative Auto-Correction metrics
        $narrativeAutofixMetrics = JTB_AI_AutoFix::getNarrativeAutofixMetrics();

        return [
            'total_sections' => $totalSections,
            'dark_sections' => $darkSections,
            'primary_sections' => $primarySections,
            'grid_sequences' => $gridSequences,
            'breathing_space_count' => $breathingSpaceCount,
            'dark_misuse_count' => $darkMisuseCount,
            'dark_misuse_patterns' => $darkMisusePatterns,
            'vi_dominant_count' => $viDominantCount,
            'vi_emphasis_count' => $viEmphasisCount,
            'vi_neutral_count' => $viNeutralCount,
            'vi_soft_count' => $viSoftCount,
            'vi_dominant_patterns' => $viDominantPatterns,
            'vd_dense_count' => $vdDenseCount,
            'vd_normal_count' => $vdNormalCount,
            'vd_sparse_count' => $vdSparseCount,
            'dense_chain_count' => $denseChainCount,
            'rhythm_breaks_count' => $rhythmBreaksCount,
            'spacing_overrides_count' => $spacingOverridesCount,
            'scale_xs' => $scaleXs,
            'scale_sm' => $scaleSm,
            'scale_md' => $scaleMd,
            'scale_lg' => $scaleLg,
            'scale_xl' => $scaleXl,
            'hero_scale' => $heroScale,
            'cta_scale' => $ctaScale,
            'typography_xl_count' => $typoXlCount ?? 0,
            'typography_lg_count' => $typoLgCount ?? 0,
            'typography_soft_count' => $typoSoftCount ?? 0,
            'hero_typography' => $heroTypography ?? null,
            'cta_typography' => $ctaTypography ?? null,
            'attention_high_count' => $attHighCount ?? 0,
            'attention_medium_count' => $attMedCount ?? 0,
            'attention_low_count' => $attLowCount ?? 0,
            'urgency_position_percent' => $urgencyPosPercent ?? null,
            'emotional_flow_signature' => $emotionalFlow ?? '',
            'has_trust_section' => $hasTrustSection ?? false,
            'has_calm_section' => $hasCalmSection ?? false,
            // Stage 17: Narrative metrics
            'narrative_signature' => $narrativeSignature ?? '',
            'narrative_score' => $narrativeScore ?? 0,
            'missing_narrative_roles' => $missingNarrativeRoles ?? [],
            'narrative_out_of_order' => $narrativeOutOfOrder ?? [],
            'broken_story_flow' => $brokenStoryFlow ?? false,
            'has_hook' => $hasHook ?? false,
            'has_promise' => $hasPromise ?? false,
            'has_proof' => $hasProof ?? false,
            'has_resolution' => $hasResolution ?? false,
            // Stage 18: Narrative Auto-Correction metrics
            'narrative_autofix_applied' => $narrativeAutofixMetrics['narrative_autofix_applied'] ?? false,
            'narrative_placeholders_count' => $narrativeAutofixMetrics['narrative_placeholders_count'] ?? 0,
            'narrative_swaps_count' => $narrativeAutofixMetrics['narrative_swaps_count'] ?? 0,
            'narrative_autofix_blocked' => $narrativeAutofixMetrics['narrative_autofix_blocked'] ?? false,
        ];
    }

    /**
     * LIGHT-only patterns - DARK context is forbidden
     * Must match JTB_AI_AutoFix::LIGHT_ONLY_PATTERNS
     */
    private const LIGHT_ONLY_PATTERNS = [
        'grid_density', 'grid_featured', 'testimonial_spotlight', 'testimonials',
        'pricing_tiered', 'pricing', 'faq_expandable', 'faq', 'contact_gateway',
        'contact', 'zigzag_narrative', 'zigzag', 'progressive_disclosure',
        'tabbed_content', 'tabs', 'breathing_space', 'visual_bridge', 'features',
        'team', 'gallery', 'portfolio', 'blog', 'newsletter', 'services',
    ];

    /**
     * DARK-allowed patterns - only these can have DARK context
     * Must match JTB_AI_AutoFix::DARK_ALLOWED_PATTERNS
     */
    private const DARK_ALLOWED_PATTERNS = [
        'trust_metrics', 'trust', 'stats', 'hero_asymmetric', 'hero_centered',
        'hero_split', 'hero',
    ];

    /**
     * Detect hard violations (automatic failures)
     */
    private static function detectHardViolations(array $patterns, array $meta): array
    {
        $violations = [];

        $hasFinalCta = false;
        $lastPattern = end($patterns);
        $lastPatternName = $lastPattern['_pattern'] ?? $lastPattern['pattern'] ?? '';

        foreach ($patterns as $pattern) {
            if ((self::getPatternName($pattern)) === 'final_cta') {
                $hasFinalCta = true;
            }
        }

        if (!$hasFinalCta) {
            $violations[] = 'MISSING_FINAL_CTA: No final_cta pattern found';
        }

        if ($hasFinalCta && $lastPatternName !== 'final_cta') {
            $violations[] = 'FINAL_CTA_NOT_LAST: final_cta must be the last section';
        }

        if ($meta['primary_sections'] !== 1) {
            $violations[] = 'PRIMARY_COUNT_INVALID: Expected exactly 1 PRIMARY section, found ' . $meta['primary_sections'];
        }

        foreach ($patterns as $pattern) {
            $ctx = $pattern['attrs']['visual_context'] ?? 'LIGHT';
            $patternName = self::getPatternName($pattern);
            if ($ctx === 'PRIMARY' && $patternName !== 'final_cta') {
                $violations[] = 'PRIMARY_MISUSE: PRIMARY context used in ' . $patternName . ' (only allowed in final_cta)';
            }
        }

        if ($meta['dark_sections'] > 2) {
            $violations[] = 'DARK_OVERFLOW: More than 2 DARK sections (' . $meta['dark_sections'] . ')';
        }

        // ============================================================
        // DARK_MISUSE: Check if DARK is used on LIGHT-only patterns
        // This is the PRIMARY violation detection for Stage 11
        // ============================================================
        $darkMisusePatterns = [];
        foreach ($patterns as $pattern) {
            $ctx = $pattern['attrs']['visual_context'] ?? 'LIGHT';
            $patternName = self::getPatternName($pattern);

            if ($ctx === 'DARK') {
                // Check if pattern is in LIGHT_ONLY list
                if (in_array($patternName, self::LIGHT_ONLY_PATTERNS)) {
                    $darkMisusePatterns[] = $patternName;
                }
                // Also check if pattern is NOT in DARK_ALLOWED list (stricter)
                elseif (!in_array($patternName, self::DARK_ALLOWED_PATTERNS) && $patternName !== 'final_cta') {
                    // Pattern is unknown - treat as LIGHT-only by default
                    $darkMisusePatterns[] = $patternName . ' (unknown)';
                }
            }
        }

        if (!empty($darkMisusePatterns)) {
            $violations[] = 'DARK_MISUSE: DARK context on LIGHT-only patterns: ' . implode(', ', array_unique($darkMisusePatterns));
        }

        // Legacy check - keep for backwards compatibility but DARK_MISUSE is more comprehensive
        $forbiddenDarkPatterns = ['grid_density', 'grid_featured', 'testimonial_spotlight', 'pricing_tiered', 'team'];
        foreach ($patterns as $pattern) {
            $ctx = $pattern['attrs']['visual_context'] ?? 'LIGHT';
            $patternName = self::getPatternName($pattern);
            if ($ctx === 'DARK' && in_array($patternName, $forbiddenDarkPatterns)) {
                // Only add if not already caught by DARK_MISUSE
                if (empty($darkMisusePatterns) || !in_array($patternName, $darkMisusePatterns)) {
                    $violations[] = 'DARK_FORBIDDEN: DARK context not allowed in ' . $patternName;
                }
            }
        }

        if ($meta['grid_sequences'] > 0) {
            $violations[] = 'GRID_SEQUENCE: Two grid patterns placed consecutively (' . $meta['grid_sequences'] . ' occurrence(s))';
        }

        $heroCta = self::extractHeroCta($patterns);
        $finalCta = self::extractFinalCta($patterns);
        if ($heroCta !== null && $finalCta !== null && strtolower($heroCta) === strtolower($finalCta)) {
            $violations[] = 'CTA_DUPLICATE: Hero CTA and Final CTA are identical ("' . $heroCta . '")';
        }

        // Stage 17: Narrative critical violations
        $narrativeValidation = JTB_AI_AutoFix::getNarrativeValidation($patterns);
        $narrativeIssues = $narrativeValidation['issues'] ?? [];
        $narrativeScore = $narrativeValidation['score'] ?? 100;

        // CTA_BEFORE_PROMISE is a critical violation
        if (in_array('CTA_BEFORE_PROMISE', $narrativeIssues)) {
            $violations[] = 'CTA_BEFORE_PROMISE: Conversion CTA appears before value proposition - broken story';
        }

        // Severely broken narrative (score < 40) is critical
        if ($narrativeScore < 40) {
            $violations[] = 'BROKEN_STORY_FLOW: Narrative score ' . $narrativeScore . '/100 - fundamental story issues';
        }

        // Stage 18: Check if auto-fix was blocked
        $narrativeAutofixMetrics = JTB_AI_AutoFix::getNarrativeAutofixMetrics();
        if ($narrativeAutofixMetrics['narrative_autofix_blocked']) {
            $violations[] = 'NARRATIVE_AUTOFIX_BLOCKED: Narrative score too low for safe auto-correction';
        }

        return $violations;
    }

    /**
     * Detect soft warnings
     */
    private static function detectWarnings(array $patterns, array $meta): array
    {
        $warnings = [];

        if ($meta['total_sections'] < 6) {
            $warnings[] = 'SHORT_PAGE: Only ' . $meta['total_sections'] . ' sections (recommended: 6-10)';
        }
        if ($meta['total_sections'] > 10) {
            $warnings[] = 'LONG_PAGE: ' . $meta['total_sections'] . ' sections (recommended: 6-10)';
        }

        if ($meta['total_sections'] > 7 && $meta['breathing_space_count'] === 0) {
            $warnings[] = 'NO_BREATHING_SPACE: Long page without breathing_space sections';
        }

        $lightStreak = 0;
        foreach ($patterns as $pattern) {
            $ctx = $pattern['attrs']['visual_context'] ?? 'LIGHT';
            if ($ctx === 'LIGHT') {
                $lightStreak++;
                if ($lightStreak > 3) {
                    $warnings[] = 'LIGHT_MONOTONY: More than 3 consecutive LIGHT sections';
                    break;
                }
            } else {
                $lightStreak = 0;
            }
        }

        // ============================================================
        // Stage 11: ALT_BG_INCOMPLETE warning
        // Check if use_background_alt=true but missing background_type or background_color
        // ============================================================
        $altBgIncomplete = [];
        foreach ($patterns as $idx => $pattern) {
            $attrs = $pattern['attrs'] ?? [];
            $useAlt = $attrs['use_background_alt'] ?? false;

            if ($useAlt) {
                $bgType = $attrs['background_type'] ?? '';
                $bgColor = $attrs['background_color'] ?? '';

                if ($bgType !== 'color' || empty($bgColor)) {
                    $patternName = self::getPatternName($pattern) ?: 'unknown';
                    $altBgIncomplete[] = "#{$idx} {$patternName}";
                }
            }
        }

        if (!empty($altBgIncomplete)) {
            $warnings[] = 'ALT_BG_INCOMPLETE: use_background_alt=true but missing background_type=color or background_color: ' . implode(', ', $altBgIncomplete);
        }

        // ============================================================
        // Stage 12: Visual Intent warnings
        // ============================================================

        // VI_CONFLICT: Multiple DOMINANT sections (should have max 1-2)
        $dominantCount = 0;
        $dominantPatterns = [];
        foreach ($patterns as $idx => $pattern) {
            $intent = $pattern['attrs']['visual_intent'] ?? $pattern['attrs']['_visual_intent'] ?? null;
            if ($intent === 'DOMINANT') {
                $dominantCount++;
                $patternName = self::getPatternName($pattern) ?: 'unknown';
                $dominantPatterns[] = "#{$idx} {$patternName}";
            }
        }

        if ($dominantCount > 2) {
            $warnings[] = 'VI_CONFLICT: ' . $dominantCount . ' DOMINANT sections (max 2 recommended): ' . implode(', ', $dominantPatterns);
        }

        // HERO_NOT_DOMINANT: Hero pattern should have DOMINANT intent
        foreach ($patterns as $idx => $pattern) {
            $patternName = self::getPatternName($pattern);
            if (str_contains($patternName, 'hero')) {
                $intent = $pattern['attrs']['visual_intent'] ?? $pattern['attrs']['_visual_intent'] ?? null;
                if ($intent !== 'DOMINANT') {
                    $warnings[] = 'HERO_NOT_DOMINANT: Hero section #' . $idx . ' has ' . ($intent ?? 'no') . ' intent instead of DOMINANT';
                }
            }
        }

        $denseStreak = 0;
        $maxDenseStreak = 0;
        foreach ($patterns as $pattern) {
            $density = $pattern['attrs']['visual_density'] ?? 'NORMAL';
            if ($density === 'DENSE') {
                $denseStreak++;
                $maxDenseStreak = max($maxDenseStreak, $denseStreak);
            } else {
                $denseStreak = 0;
            }
        }
        if ($maxDenseStreak > 2) {
            $warnings[] = 'DENSE_CHAIN: ' . $maxDenseStreak . ' consecutive DENSE sections without rhythm break';
        }

        $hasClimax = false;
        foreach ($patterns as $pattern) {
            $patternName = self::getPatternName($pattern);
            if (str_contains($patternName, 'final_cta') || str_contains($patternName, 'cta')) {
                $before = $pattern['attrs']['before_spacing'] ?? null;
                if ($before === '2xl' || $before === 'xl') {
                    $hasClimax = true;
                }
            }
        }
        if (!$hasClimax && count($patterns) >= 5) {
            $warnings[] = 'NO_CLIMAX: final_cta missing 2xl/xl spacing for climax effect';
        }

        foreach ($patterns as $idx => $pattern) {
            $density = $pattern['attrs']['visual_density'] ?? 'NORMAL';
            if ($density === 'SPARSE') {
                $before = $pattern['attrs']['before_spacing'] ?? null;
                $after = $pattern['attrs']['after_spacing'] ?? null;
                $spacingMap = ['sm' => 1, 'md' => 2, 'lg' => 3, 'xl' => 4, '2xl' => 5];
                $beforeVal = $spacingMap[$before] ?? 0;
                $afterVal = $spacingMap[$after] ?? 0;
                if ($beforeVal < 2 || $afterVal < 2) {
                    $patternName = self::getPatternName($pattern) ?: 'unknown';
                    $warnings[] = 'SPARSE_TOO_TIGHT: SPARSE section #' . $idx . ' (' . $patternName . ') needs >= md spacing';
                    break;
                }
            }
        }

        $heroScale = null;
        $ctaScale = null;
        $xlCount = 0;

        foreach ($patterns as $pattern) {
            $scale = $pattern['attrs']['visual_scale'] ?? 'MD';
            $patternName = self::getPatternName($pattern);

            if (strtoupper($scale) === 'XL') {
                $xlCount++;
            }

            if (strpos($patternName, 'hero') === 0 && $heroScale === null) {
                $heroScale = strtoupper($scale);
            }
            if ($patternName === 'final_cta' || $patternName === 'cta') {
                $ctaScale = strtoupper($scale);
            }
        }

        if ($heroScale !== null && !in_array($heroScale, ['LG', 'XL'])) {
            $warnings[] = 'HERO_UNDER_SCALED: Hero has ' . $heroScale . ' scale (should be LG or XL)';
        }

        if ($ctaScale !== null && !in_array($ctaScale, ['LG', 'XL'])) {
            $warnings[] = 'CTA_NOT_CLIMAX: final_cta has ' . $ctaScale . ' scale (should be LG or XL)';
        }

        if ($xlCount > 2) {
            $warnings[] = 'MULTI_XL: ' . $xlCount . ' sections with XL scale (max 2 recommended)';
        }

        $heroTypo = null;
        $ctaTypo = null;
        foreach ($patterns as $pattern) {
            $patternName = self::getPatternName($pattern);
            $typoScale = $pattern['attrs']['typography_scale'] ?? 'MD';

            if (strpos($patternName, 'hero') === 0 && $heroTypo === null) {
                $heroTypo = strtoupper($typoScale);
            }
            if ($patternName === 'final_cta' || $patternName === 'cta') {
                $ctaTypo = strtoupper($typoScale);
            }
        }

        if ($heroTypo !== null && !in_array($heroTypo, ['LG', 'XL'])) {
            $warnings[] = 'HERO_TYPO_TOO_WEAK: Hero typography ' . $heroTypo . ' (should be LG or XL)';
        }

        if ($ctaTypo !== null && !in_array($ctaTypo, ['LG', 'XL'])) {
            $warnings[] = 'CTA_TYPO_NOT_CLIMAX: CTA typography ' . $ctaTypo . ' (should be LG or XL)';
        }

        $consecutiveHigh = 0;
        $maxConsecutiveHigh = 0;
        $hasTrust = false;
        $hasCalm = false;
        $urgencyIdx = null;

        foreach ($patterns as $idx => $pattern) {
            $attention = $pattern['attrs']['attention_level'] ?? 'medium';
            $tone = $pattern['attrs']['emotional_tone'] ?? 'focus';

            if ($attention === 'high') {
                $consecutiveHigh++;
                $maxConsecutiveHigh = max($maxConsecutiveHigh, $consecutiveHigh);
            } else {
                $consecutiveHigh = 0;
            }

            if ($tone === 'trust') $hasTrust = true;
            if ($tone === 'calm') $hasCalm = true;
            if ($tone === 'urgency' && $urgencyIdx === null) $urgencyIdx = $idx;
        }

        if ($maxConsecutiveHigh > 2) {
            $warnings[] = 'ATTENTION_OVERLOAD: ' . $maxConsecutiveHigh . ' consecutive HIGH attention sections';
        }

        if (!$hasTrust && count($patterns) >= 5) {
            $warnings[] = 'NO_TRUST_SECTION: No trust-building sections (testimonials, stats)';
        }

        if (!$hasCalm && count($patterns) >= 7) {
            $warnings[] = 'NO_CALM_SECTION: No calm sections for rhythm break';
        }

        if ($urgencyIdx !== null && count($patterns) > 0) {
            $urgencyPercent = ($urgencyIdx / count($patterns)) * 100;
            if ($urgencyPercent < 60) {
                $warnings[] = 'URGENCY_TOO_EARLY: Urgency at ' . round($urgencyPercent) . '% (should be >60%)';
            }
        }

        // FLAT_FLOW: No emotional variety (all same tone)
        $uniqueTones = [];
        foreach ($patterns as $pattern) {
            $tone = $pattern['attrs']['emotional_tone'] ?? 'focus';
            $uniqueTones[$tone] = true;
        }
        if (count($patterns) >= 5 && count($uniqueTones) <= 1) {
            $warnings[] = 'FLAT_FLOW: No emotional variety (all sections have same tone)';
        }

        // Stage 17: Narrative flow warnings
        $narrativeValidation = JTB_AI_AutoFix::getNarrativeValidation($patterns);
        $narrativeIssues = $narrativeValidation['issues'] ?? [];
        $missingRoles = $narrativeValidation['missing_roles'] ?? [];
        $narrativeScore = $narrativeValidation['score'] ?? 100;

        // Missing essential roles
        if (in_array('PROOF', $missingRoles)) {
            $warnings[] = 'NO_PROOF: Missing social proof sections (testimonials, stats)';
        }
        if (in_array('HOOK', $missingRoles)) {
            $warnings[] = 'NO_HOOK: Missing attention-grabbing hero section';
        }
        if (in_array('PROMISE', $missingRoles)) {
            $warnings[] = 'NO_PROMISE: Missing value proposition (features, benefits)';
        }

        // Out of order issues
        if (in_array('CTA_BEFORE_PROMISE', $narrativeIssues)) {
            $warnings[] = 'CTA_BEFORE_PROMISE: CTA appears before showing value (critical story break)';
        }
        if (in_array('PROOF_BEFORE_PROMISE', $narrativeIssues)) {
            $warnings[] = 'PROOF_BEFORE_PROMISE: Proof shown before explaining what we offer';
        }

        // Broken story flow
        if ($narrativeScore < 40) {
            $warnings[] = 'BROKEN_STORY_FLOW: Narrative score ' . $narrativeScore . '/100 (needs restructuring)';
        }

        // Stage 18: Narrative Auto-Correction warnings
        $narrativeAutofixMetrics = JTB_AI_AutoFix::getNarrativeAutofixMetrics();

        if ($narrativeAutofixMetrics['narrative_autofix_applied']) {
            if ($narrativeAutofixMetrics['narrative_placeholders_count'] > 0) {
                $warnings[] = 'NARRATIVE_PLACEHOLDER_USED: ' . $narrativeAutofixMetrics['narrative_placeholders_count'] . ' placeholder section(s) inserted';
            }
            if ($narrativeAutofixMetrics['narrative_swaps_count'] > 0) {
                $warnings[] = 'NARRATIVE_SWAP_APPLIED: ' . $narrativeAutofixMetrics['narrative_swaps_count'] . ' section swap(s) performed';
            }
        }

        if ($narrativeAutofixMetrics['narrative_autofix_blocked']) {
            $warnings[] = 'NARRATIVE_AUTOFIX_BLOCKED: Score too low for safe auto-correction';
        }

        return $warnings;
    }

    /**
     * Score rhythm (0-5)
     */
    private static function scoreRhythm(array $patterns, array $meta): int
    {
        $score = 2;

        if ($meta['breathing_space_count'] > 0) {
            $score++;
        }

        if (self::hasDenseSparsePattern($patterns)) {
            $score++;
        }

        $consecutiveDense = self::countConsecutiveDense($patterns);
        if ($consecutiveDense >= 2) {
            $score--;
        }

        $lightStreak = self::maxLightStreak($patterns);
        if ($lightStreak > 3) {
            $score -= 2;
        }

        return max(0, min(5, $score));
    }

    /**
     * Score hierarchy (0-5)
     */
    private static function scoreHierarchy(array $patterns, array $meta): int
    {
        $score = 0;

        if (!empty($patterns)) {
            $first = $patterns[0]['_pattern'] ?? $patterns[0]['pattern'] ?? '';
            if (str_contains($first, 'hero')) {
                $score++;
            }
        }

        $lastPattern = end($patterns);
        if (($lastPattern['_pattern'] ?? $lastPattern['pattern'] ?? '') === 'final_cta') {
            $score++;
        }

        foreach ($patterns as $pattern) {
            $p = self::getPatternName($pattern);
            if ($p === 'trust_metrics' || $p === 'testimonial_spotlight') {
                $score++;
                break;
            }
        }

        $heroCta = self::extractHeroCta($patterns);
        $finalCta = self::extractFinalCta($patterns);
        if ($heroCta !== null && $finalCta !== null && strtolower($heroCta) !== strtolower($finalCta)) {
            $score++;
        }

        if (!self::hasSequentialPatternRepeat($patterns)) {
            $score++;
        }

        return max(0, min(5, $score));
    }

    /**
     * Score contrast (0-5)
     */
    private static function scoreContrast(array $patterns, array $meta): int
    {
        $score = 0;

        foreach ($patterns as $pattern) {
            $p = self::getPatternName($pattern);
            $ctx = $pattern['attrs']['visual_context'] ?? 'LIGHT';
            if ($p === 'trust_metrics' && $ctx === 'DARK') {
                $score += 2;
                break;
            }
        }

        if (self::hasBackgroundAlternation($patterns)) {
            $score++;
        }

        $primaryOnlyInFinalCta = true;
        foreach ($patterns as $pattern) {
            $ctx = $pattern['attrs']['visual_context'] ?? 'LIGHT';
            $p = self::getPatternName($pattern);
            if ($ctx === 'PRIMARY' && $p !== 'final_cta') {
                $primaryOnlyInFinalCta = false;
                break;
            }
        }
        if ($primaryOnlyInFinalCta && $meta['primary_sections'] === 1) {
            $score += 2;
        }

        return max(0, min(5, $score));
    }

    /**
     * Score content uniqueness (0-5)
     */
    private static function scoreContent(array $patterns): int
    {
        $score = 3;

        $genericHeadlines = ['welcome', 'about us', 'our services', 'contact'];
        foreach ($patterns as $pattern) {
            $rows = $pattern['rows'] ?? [];
            foreach ($rows as $row) {
                $columns = $row['children'] ?? [];
                foreach ($columns as $col) {
                    $modules = $col['children'] ?? [];
                    foreach ($modules as $module) {
                        if (($module['type'] ?? '') === 'heading') {
                            $text = strtolower($module['attrs']['text'] ?? '');
                            foreach ($genericHeadlines as $generic) {
                                if ($text === $generic) {
                                    $score--;
                                    break 4;
                                }
                            }
                        }
                    }
                }
            }
        }

        $icons = self::collectBlurbIcons($patterns);
        $iconCounts = array_count_values($icons);
        foreach ($iconCounts as $count) {
            if ($count > 3) {
                $score--;
                break;
            }
        }

        foreach ($patterns as $pattern) {
            if ((self::getPatternName($pattern)) === 'testimonial_spotlight') {
                $rows = $pattern['rows'] ?? [];
                foreach ($rows as $row) {
                    $columns = $row['children'] ?? [];
                    foreach ($columns as $col) {
                        $modules = $col['children'] ?? [];
                        foreach ($modules as $module) {
                            if (($module['type'] ?? '') === 'testimonial') {
                                $attrs = $module['attrs'] ?? [];
                                if (empty($attrs['author']) || empty($attrs['job_title']) || empty($attrs['company'])) {
                                    $score--;
                                    break 4;
                                }
                            }
                        }
                    }
                }
            }
        }

        if (self::metricsHaveSuffix($patterns)) {
            $score++;
        }

        if (self::pricingHasFeatured($patterns)) {
            $score++;
        }

        return max(0, min(5, $score));
    }

    /**
     * Score conversion (0-5)
     */
    private static function scoreConversion(array $patterns, array $meta): int
    {
        $score = 0;

        $heroCta = self::extractHeroCta($patterns);
        $lowCommitment = ['explore', 'learn', 'see', 'discover', 'view', 'how it works'];
        if ($heroCta !== null) {
            $heroCtaLower = strtolower($heroCta);
            foreach ($lowCommitment as $phrase) {
                if (str_contains($heroCtaLower, $phrase)) {
                    $score++;
                    break;
                }
            }
        }

        foreach ($patterns as $pattern) {
            $p = self::getPatternName($pattern);
            if ($p === 'pricing_tiered' || $p === 'faq_expandable') {
                $score++;
                break;
            }
        }

        $finalCta = self::extractFinalCta($patterns);
        $highCommitment = ['trial', 'buy', 'start', 'sign up', 'get started', 'subscribe', 'purchase', 'order'];
        if ($finalCta !== null) {
            $finalCtaLower = strtolower($finalCta);
            foreach ($highCommitment as $phrase) {
                if (str_contains($finalCtaLower, $phrase)) {
                    $score += 2;
                    break;
                }
            }
        }

        $faqIndex = null;
        $finalCtaIndex = null;
        foreach ($patterns as $i => $pattern) {
            $p = self::getPatternName($pattern);
            if ($p === 'faq_expandable') {
                $faqIndex = $i;
            }
            if ($p === 'final_cta') {
                $finalCtaIndex = $i;
            }
        }
        if ($faqIndex !== null && $finalCtaIndex !== null && $faqIndex < $finalCtaIndex) {
            $score++;
        }

        return max(0, min(5, $score));
    }

    // ========================================
    // Scoring Helper Methods
    // ========================================

    private static function extractHeroCta(array $patterns): ?string
    {
        foreach ($patterns as $pattern) {
            $p = self::getPatternName($pattern);
            if (str_contains($p, 'hero')) {
                $rows = $pattern['rows'] ?? [];
                foreach ($rows as $row) {
                    $columns = $row['children'] ?? [];
                    foreach ($columns as $col) {
                        $modules = $col['children'] ?? [];
                        foreach ($modules as $module) {
                            if (($module['type'] ?? '') === 'button') {
                                return $module['attrs']['text'] ?? null;
                            }
                        }
                    }
                }
            }
        }
        return null;
    }

    private static function extractFinalCta(array $patterns): ?string
    {
        foreach ($patterns as $pattern) {
            if ((self::getPatternName($pattern)) === 'final_cta') {
                $rows = $pattern['rows'] ?? [];
                foreach ($rows as $row) {
                    $columns = $row['children'] ?? [];
                    foreach ($columns as $col) {
                        $modules = $col['children'] ?? [];
                        foreach ($modules as $module) {
                            if (($module['type'] ?? '') === 'button') {
                                return $module['attrs']['text'] ?? null;
                            }
                        }
                    }
                }
            }
        }
        return null;
    }

    private static function hasDenseSparsePattern(array $patterns): bool
    {
        $densePatterns = ['grid_density', 'grid_featured', 'testimonial_spotlight', 'pricing_tiered', 'team'];
        $sparsePatterns = ['breathing_space', 'hero_split', 'hero_centered', 'final_cta', 'zigzag_narrative'];

        $denseSeen = false;
        $sparseAfterDense = false;
        $denseAfterSparse = false;

        foreach ($patterns as $pattern) {
            $p = self::getPatternName($pattern);
            $isDense = in_array($p, $densePatterns);
            $isSparse = in_array($p, $sparsePatterns);

            if ($isDense) {
                if ($sparseAfterDense) {
                    $denseAfterSparse = true;
                }
                $denseSeen = true;
            }
            if ($isSparse && $denseSeen) {
                $sparseAfterDense = true;
            }
        }

        return $denseAfterSparse;
    }

    private static function countConsecutiveDense(array $patterns): int
    {
        $densePatterns = ['grid_density', 'grid_featured', 'testimonial_spotlight', 'pricing_tiered', 'team'];
        $maxConsecutive = 0;
        $current = 0;

        foreach ($patterns as $pattern) {
            $p = self::getPatternName($pattern);
            if (in_array($p, $densePatterns)) {
                $current++;
                $maxConsecutive = max($maxConsecutive, $current);
            } else {
                $current = 0;
            }
        }

        return $maxConsecutive;
    }

    private static function maxLightStreak(array $patterns): int
    {
        $maxStreak = 0;
        $current = 0;

        foreach ($patterns as $pattern) {
            $ctx = $pattern['attrs']['visual_context'] ?? 'LIGHT';
            if ($ctx === 'LIGHT') {
                $current++;
                $maxStreak = max($maxStreak, $current);
            } else {
                $current = 0;
            }
        }

        return $maxStreak;
    }

    private static function hasSequentialPatternRepeat(array $patterns): bool
    {
        $prevPattern = null;
        foreach ($patterns as $pattern) {
            $p = self::getPatternName($pattern);
            if ($prevPattern !== null && $p === $prevPattern) {
                return true;
            }
            $prevPattern = $p;
        }
        return false;
    }

    private static function hasBackgroundAlternation(array $patterns): bool
    {
        // Check for visual_context changes OR use_background_alt alternation
        $backgrounds = [];
        foreach ($patterns as $pattern) {
            $context = $pattern['attrs']['visual_context'] ?? 'LIGHT';
            $useAlt = $pattern['attrs']['use_background_alt'] ?? false;

            // Create composite key: LIGHT vs LIGHT_ALT vs DARK vs PRIMARY
            if ($context === 'LIGHT' && $useAlt) {
                $backgrounds[] = 'LIGHT_ALT';
            } else {
                $backgrounds[] = $context;
            }
        }

        // Check for ANY change in background style
        for ($i = 1; $i < count($backgrounds); $i++) {
            if ($backgrounds[$i] !== $backgrounds[$i - 1]) {
                return true;
            }
        }
        return false;
    }

    private static function collectBlurbIcons(array $patterns): array
    {
        $icons = [];
        foreach ($patterns as $pattern) {
            $rows = $pattern['rows'] ?? [];
            foreach ($rows as $row) {
                $columns = $row['children'] ?? [];
                foreach ($columns as $col) {
                    $modules = $col['children'] ?? [];
                    foreach ($modules as $module) {
                        if (($module['type'] ?? '') === 'blurb') {
                            $icon = $module['attrs']['font_icon'] ?? $module['attrs']['icon'] ?? '';
                            if (!empty($icon)) {
                                $icons[] = $icon;
                            }
                        }
                    }
                }
            }
        }
        return $icons;
    }

    private static function metricsHaveSuffix(array $patterns): bool
    {
        $suffixPatterns = ['%', '+', 'K', 'M', '★', 'k', 'm'];
        foreach ($patterns as $pattern) {
            if ((self::getPatternName($pattern)) === 'trust_metrics') {
                $rows = $pattern['rows'] ?? [];
                foreach ($rows as $row) {
                    $columns = $row['children'] ?? [];
                    foreach ($columns as $col) {
                        $modules = $col['children'] ?? [];
                        foreach ($modules as $module) {
                            if (($module['type'] ?? '') === 'number_counter') {
                                $suffix = $module['attrs']['percent_sign'] ?? $module['attrs']['suffix'] ?? '';
                                foreach ($suffixPatterns as $s) {
                                    if (str_contains($suffix, $s)) {
                                        return true;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        return false;
    }

    private static function pricingHasFeatured(array $patterns): bool
    {
        foreach ($patterns as $pattern) {
            if ((self::getPatternName($pattern)) === 'pricing_tiered') {
                $rows = $pattern['rows'] ?? [];
                foreach ($rows as $row) {
                    $columns = $row['children'] ?? [];
                    foreach ($columns as $col) {
                        $modules = $col['children'] ?? [];
                        foreach ($modules as $module) {
                            if (($module['type'] ?? '') === 'pricing_table') {
                                if (!empty($module['attrs']['featured'])) {
                                    return true;
                                }
                            }
                        }
                    }
                }
            }
        }
        return false;
    }

    /**
     * Dynamically detect which module types should get card-style column styling.
     * A "card module" is one that has a title/name AND a content/description field,
     * making it a self-contained content block suitable for card presentation.
     * @return array List of module slugs that should have card styling
     */
    private static function getCardStyleModuleTypes(): array
    {
        static $cache = null;
        if ($cache !== null) {
            return $cache;
        }

        $cache = [];
        $structureTypes = ['section', 'row', 'column'];

        try {
            $registry = JTB_Registry::all();
            foreach ($registry as $slug => $className) {
                if (in_array($slug, $structureTypes)) continue;

                $instance = JTB_Registry::get($slug);
                if (!$instance) continue;

                $fields = $instance->getFields();
                $fieldNames = array_keys($fields);

                // Check for card indicators: has title/name AND content/description
                $hasTitleField = !empty(array_intersect($fieldNames, ['title', 'name', 'plan_name']));
                $hasContentField = !empty(array_intersect($fieldNames, ['content', 'description', 'bio', 'body']));

                if ($hasTitleField && $hasContentField) {
                    $cache[] = $slug;
                }
            }
        } catch (\Exception $e) {
            // Minimal fallback if Registry fails
            $cache = ['blurb', 'testimonial', 'pricing_table', 'team_member'];
        }

        return $cache;
    }

    /**
     * Get default AI provider from config (no hardcodes!)
     */
    private static function getDefaultProvider(): string
    {
        $settingsPath = CMS_ROOT . '/config/ai_settings.json';
        if (file_exists($settingsPath)) {
            $settings = @json_decode(file_get_contents($settingsPath), true);
            if (!empty($settings['default_provider'])) {
                return $settings['default_provider'];
            }
        }
        return 'anthropic'; // fallback only if config missing
    }
}
