<?php
/**
 * JTB AI Auto-Fix Engine (SAFE VERSION)
 * Pattern-aware deterministic layout mutations based on quality violations
 *
 * GOLDEN RULES ENFORCED:
 * - DARK only for trust_metrics (1-2 max) + optionally hero
 * - LIGHT-only patterns: features, testimonials, pricing, faq, team, contact, etc.
 * - PRIMARY only for final_cta (must be last)
 * - Light alternation via background_alt instead of changing to DARK
 *
 * @package JessieThemeBuilder
 * @since 2.2.0
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

class JTB_AI_AutoFix
{
    /**
     * Applied fixes log (for debug visibility)
     */
    private static array $appliedFixes = [];

    /**
     * Patterns that MUST be LIGHT only - cannot be DARK
     */
    private const LIGHT_ONLY_PATTERNS = [
        'grid_density',
        'grid_featured',
        'testimonial_spotlight',
        'testimonials',
        'pricing_tiered',
        'pricing',
        'faq_expandable',
        'faq',
        'contact_gateway',
        'contact',
        'zigzag_narrative',
        'zigzag',
        'progressive_disclosure',
        'tabbed_content',
        'tabs',
        'breathing_space',
        'visual_bridge',
        'features',
        'team',
        'gallery',
        'portfolio',
        'blog',
        'newsletter',
        'services',
    ];

    /**
     * Patterns that can be DARK (trust building, hero)
     */
    private const DARK_ALLOWED_PATTERNS = [
        'trust_metrics',
        'trust',
        'stats',
        'hero_asymmetric',
        'hero_centered',
        'hero_split',
        'hero',
    ];

    /**
     * Patterns that MUST be PRIMARY (final CTA)
     */
    private const PRIMARY_ONLY_PATTERNS = [
        'final_cta',
        'final_cta_simple',
        'cta',
    ];

    /**
     * Stage 12: Pattern to Visual Intent mapping
     * DOMINANT = highest visual weight (hero, final_cta)
     * EMPHASIS = high visual weight (trust_metrics, pricing)
     * NEUTRAL  = standard weight (features, services)
     * SOFT     = low visual weight (faq, footer, dividers)
     */
    private const PATTERN_INTENT_MAP = [
        // DOMINANT
        'hero' => 'DOMINANT',
        'hero_asymmetric' => 'DOMINANT',
        'hero_centered' => 'DOMINANT',
        'hero_split' => 'DOMINANT',
        'final_cta' => 'DOMINANT',
        'final_cta_simple' => 'DOMINANT',

        // EMPHASIS
        'trust_metrics' => 'EMPHASIS',
        'trust' => 'EMPHASIS',
        'stats' => 'EMPHASIS',
        'pricing' => 'EMPHASIS',
        'pricing_tiered' => 'EMPHASIS',
        'testimonial_spotlight' => 'EMPHASIS',

        // NEUTRAL (default for most)
        'features' => 'NEUTRAL',
        'grid_density' => 'NEUTRAL',
        'grid_featured' => 'NEUTRAL',
        'services' => 'NEUTRAL',
        'portfolio' => 'NEUTRAL',
        'gallery' => 'NEUTRAL',
        'team' => 'NEUTRAL',
        'blog' => 'NEUTRAL',
        'zigzag' => 'NEUTRAL',
        'zigzag_narrative' => 'NEUTRAL',
        'testimonials' => 'NEUTRAL',
        'tabbed_content' => 'NEUTRAL',
        'tabs' => 'NEUTRAL',
        'progressive_disclosure' => 'NEUTRAL',
        'contact' => 'NEUTRAL',
        'contact_gateway' => 'NEUTRAL',
        'newsletter' => 'NEUTRAL',

        // SOFT
        'faq' => 'SOFT',
        'faq_expandable' => 'SOFT',
        'breathing_space' => 'SOFT',
        'visual_bridge' => 'SOFT',
        'divider' => 'SOFT',
        'footer' => 'SOFT',
    ];

    private const PATTERN_DENSITY_MAP = [
        'hero' => 'DENSE',
        'hero_asymmetric' => 'DENSE',
        'hero_centered' => 'DENSE',
        'hero_split' => 'DENSE',
        'features' => 'DENSE',
        'grid_density' => 'DENSE',
        'grid_featured' => 'DENSE',
        'pricing' => 'DENSE',
        'pricing_tiered' => 'DENSE',
        'trust_metrics' => 'DENSE',
        'testimonial_spotlight' => 'NORMAL',
        'testimonials' => 'NORMAL',
        'zigzag' => 'NORMAL',
        'zigzag_narrative' => 'NORMAL',
        'stats' => 'NORMAL',
        'services' => 'NORMAL',
        'portfolio' => 'NORMAL',
        'gallery' => 'NORMAL',
        'blog' => 'NORMAL',
        'newsletter' => 'NORMAL',
        'tabbed_content' => 'NORMAL',
        'tabs' => 'NORMAL',
        'progressive_disclosure' => 'NORMAL',
        'faq' => 'SPARSE',
        'faq_expandable' => 'SPARSE',
        'breathing_space' => 'SPARSE',
        'visual_bridge' => 'SPARSE',
        'contact' => 'SPARSE',
        'contact_gateway' => 'SPARSE',
        'team' => 'SPARSE',
        'footer' => 'SPARSE',
        'divider' => 'SPARSE',
        'final_cta' => 'NORMAL',
        'final_cta_simple' => 'NORMAL',
    ];

    /**
     * Stage 17: Pattern to Narrative Role mapping
     * Story beats: HOOK → PROBLEM → PROMISE → PROOF → DETAILS → RELIEF → RESOLUTION
     *
     * HOOK       = opening that grabs attention (hero)
     * PROBLEM    = what's wrong, pain points (problem sections)
     * PROMISE    = what we offer, features/benefits (features, services)
     * PROOF      = social proof, trust signals (testimonials, stats, trust_metrics)
     * DETAILS    = specifics, pricing, FAQ (pricing, faq)
     * RELIEF     = ease tension, breathing room (breathing_space, contact)
     * RESOLUTION = final action, closure (final_cta)
     */
    private const PATTERN_NARRATIVE_ROLE_MAP = [
        // HOOK - opening attention grabbers
        'hero' => 'HOOK',
        'hero_asymmetric' => 'HOOK',
        'hero_centered' => 'HOOK',
        'hero_split' => 'HOOK',

        // PROBLEM - pain point sections
        'problem' => 'PROBLEM',
        'problem_agitation' => 'PROBLEM',
        'challenges' => 'PROBLEM',

        // PROMISE - what we offer
        'features' => 'PROMISE',
        'grid_density' => 'PROMISE',
        'grid_featured' => 'PROMISE',
        'services' => 'PROMISE',
        'benefits' => 'PROMISE',
        'zigzag' => 'PROMISE',
        'zigzag_narrative' => 'PROMISE',

        // PROOF - social proof and trust
        'trust_metrics' => 'PROOF',
        'trust' => 'PROOF',
        'testimonials' => 'PROOF',
        'testimonial_spotlight' => 'PROOF',
        'stats' => 'PROOF',
        'logos' => 'PROOF',
        'awards' => 'PROOF',
        'case_studies' => 'PROOF',
        'portfolio' => 'PROOF',

        // DETAILS - specifics and information
        'pricing' => 'DETAILS',
        'pricing_tiered' => 'DETAILS',
        'faq' => 'DETAILS',
        'faq_expandable' => 'DETAILS',
        'tabbed_content' => 'DETAILS',
        'tabs' => 'DETAILS',
        'progressive_disclosure' => 'DETAILS',
        'comparison' => 'DETAILS',
        'how_it_works' => 'DETAILS',

        // RELIEF - ease tension, breathing room
        'breathing_space' => 'RELIEF',
        'visual_bridge' => 'RELIEF',
        'contact' => 'RELIEF',
        'contact_gateway' => 'RELIEF',
        'newsletter' => 'RELIEF',
        'team' => 'RELIEF',
        'about' => 'RELIEF',

        // RESOLUTION - final action
        'final_cta' => 'RESOLUTION',
        'final_cta_simple' => 'RESOLUTION',
        'cta' => 'RESOLUTION',
    ];

    /**
     * Ideal narrative order (used for validation)
     */
    private const NARRATIVE_IDEAL_ORDER = [
        'HOOK',      // 1. Grab attention
        'PROBLEM',   // 2. Identify pain (optional)
        'PROMISE',   // 3. Show what we offer
        'PROOF',     // 4. Prove it works
        'DETAILS',   // 5. Provide specifics
        'RELIEF',    // 6. Ease tension
        'RESOLUTION' // 7. Call to action
    ];

    private const PATTERN_EMOTIONAL_MAP = [
        'hero' => ['tone' => 'focus', 'attention' => 'high'],
        'hero_asymmetric' => ['tone' => 'focus', 'attention' => 'high'],
        'hero_centered' => ['tone' => 'focus', 'attention' => 'high'],
        'hero_split' => ['tone' => 'focus', 'attention' => 'high'],
        'trust_metrics' => ['tone' => 'trust', 'attention' => 'medium'],
        'trust' => ['tone' => 'trust', 'attention' => 'medium'],
        'stats' => ['tone' => 'trust', 'attention' => 'medium'],
        'testimonials' => ['tone' => 'trust', 'attention' => 'medium'],
        'testimonial_spotlight' => ['tone' => 'trust', 'attention' => 'medium'],
        'features' => ['tone' => 'focus', 'attention' => 'medium'],
        'grid_density' => ['tone' => 'focus', 'attention' => 'medium'],
        'grid_featured' => ['tone' => 'focus', 'attention' => 'medium'],
        'services' => ['tone' => 'focus', 'attention' => 'medium'],
        'pricing' => ['tone' => 'focus', 'attention' => 'medium'],
        'pricing_tiered' => ['tone' => 'focus', 'attention' => 'medium'],
        'portfolio' => ['tone' => 'focus', 'attention' => 'medium'],
        'gallery' => ['tone' => 'focus', 'attention' => 'medium'],
        'blog' => ['tone' => 'focus', 'attention' => 'medium'],
        'zigzag' => ['tone' => 'focus', 'attention' => 'medium'],
        'zigzag_narrative' => ['tone' => 'focus', 'attention' => 'medium'],
        'newsletter' => ['tone' => 'focus', 'attention' => 'medium'],
        'contact' => ['tone' => 'focus', 'attention' => 'medium'],
        'contact_gateway' => ['tone' => 'focus', 'attention' => 'medium'],
        'team' => ['tone' => 'trust', 'attention' => 'low'],
        'faq' => ['tone' => 'calm', 'attention' => 'low'],
        'faq_expandable' => ['tone' => 'calm', 'attention' => 'low'],
        'breathing_space' => ['tone' => 'calm', 'attention' => 'low'],
        'visual_bridge' => ['tone' => 'calm', 'attention' => 'low'],
        'divider' => ['tone' => 'calm', 'attention' => 'low'],
        'footer' => ['tone' => 'calm', 'attention' => 'low'],
        'final_cta' => ['tone' => 'urgency', 'attention' => 'high'],
        'final_cta_simple' => ['tone' => 'urgency', 'attention' => 'high'],
        'cta' => ['tone' => 'urgency', 'attention' => 'high'],
    ];

    /**
     * Simple process wrapper for direct AI generation
     * Applies fixes without quality report (generates basic quality from layout)
     *
     * @param array $layout  The layout to fix (with 'sections' key)
     * @param array $options Optional context options
     * @return array Modified layout
     */
    public static function process(array $layout, array $options = []): array
    {
        // Generate basic quality report from layout structure
        $sections = $layout['sections'] ?? [];
        $quality = [
            'violations' => [],
            'warnings' => [],
            'total_sections' => count($sections)
        ];

        // Run applyFixes
        $fixed = self::applyFixes($layout, $quality);

        return $fixed;
    }

    /**
     * Main entry point - apply all relevant fixes based on quality report
     *
     * @param array $layout   The layout to fix (sections array)
     * @param array $quality  Quality report with violations/warnings
     * @return array Modified layout with _autofix metadata
     */
    public static function applyFixes(array $layout, array $quality): array
    {
        self::$appliedFixes = [];

        $sections = $layout['sections'] ?? $layout['content'] ?? $layout;
        if (!is_array($sections)) {
            return $layout;
        }

        $violations = $quality['violations'] ?? [];
        $warnings = $quality['warnings'] ?? [];
        $allIssues = array_merge($violations, $warnings);

        // Extract issue codes
        $issueCodes = array_map(function($issue) {
            return is_array($issue) ? ($issue['code'] ?? $issue['rule'] ?? '') : (string)$issue;
        }, $allIssues);

        // Apply fixes in priority order (most critical first)

        // 1. Fix DARK_MISUSE first - change LIGHT-only patterns back to LIGHT
        $sections = self::fixDarkMisuse($sections);

        // 2. Fix PRIMARY - ensure only final_cta has PRIMARY
        $sections = self::fixPrimaryCountInvalid($sections, $issueCodes, $quality);

        // 3. Add missing final CTA
        $sections = self::fixMissingFinalCta($sections, $issueCodes);

        // 4. Ensure final_cta is last
        $sections = self::ensureFinalCtaIsLast($sections);

        // 5. Ensure trust_metrics has DARK context (improves contrast score +2)
        $sections = self::ensureTrustMetricsDark($sections);

        // 6. Fix DARK overflow (max 2 DARK sections, only on allowed patterns)
        $sections = self::fixDarkOverflow($sections);

        // 7. Safe light alternation (NOT changing to DARK)
        $sections = self::fixLightMonotonySafe($sections, $issueCodes);

        // 8. Long page fix
        $sections = self::fixLongPage($sections, $issueCodes);

        // 9. Grid density fix
        $sections = self::fixGridDensityLow($sections, $issueCodes);

        // 10. Stage 12: Apply Visual Intent
        $sections = self::applyVisualIntent($sections);

        // 11. Stage 13: Apply Visual Density
        $sections = self::applyVisualDensity($sections);

        // 12. Stage 13: Apply Visual Rhythm
        $sections = self::applyVisualRhythm($sections);

        // 13. Stage 14: Apply Visual Scale
        $sections = self::applyVisualScale($sections, $quality);

        // 14. Stage 15: Apply Typography Intent
        $sections = self::applyTypographyIntent($sections);

        // 15. Stage 16: Apply Emotional Flow
        $sections = self::applyEmotionalFlow($sections);

        // 16. Stage 17: Apply Narrative Roles
        $sections = self::applyNarrativeRoles($sections);

        // 17. Stage 17: Validate and fix narrative flow
        $sections = self::fixNarrativeFlow($sections);

        // 18. Stage 18: Narrative Auto-Correction (SAFE & DETERMINISTIC)
        $sections = self::applyNarrativeAutoFix($sections, $quality);

        // Return in original format
        if (isset($layout['sections'])) {
            $layout['sections'] = $sections;
        } elseif (isset($layout['content'])) {
            $layout['content'] = $sections;
        } else {
            $layout = $sections;
        }

        // Add metadata about applied fixes
        $layout['_autofix_applied'] = self::$appliedFixes;
        $layout['_autofix_count'] = count(self::$appliedFixes);

        return $layout;
    }

    /**
     * Get list of applied fixes (for debug display)
     */
    public static function getAppliedFixes(): array
    {
        return self::$appliedFixes;
    }

    /**
     * Check if pattern is LIGHT-only
     */
    public static function isLightOnlyPattern(string $pattern): bool
    {
        $basePattern = self::getBasePattern($pattern);
        return in_array($basePattern, self::LIGHT_ONLY_PATTERNS);
    }

    /**
     * Check if pattern allows DARK
     */
    public static function isDarkAllowedPattern(string $pattern): bool
    {
        $basePattern = self::getBasePattern($pattern);
        return in_array($basePattern, self::DARK_ALLOWED_PATTERNS);
    }

    /**
     * Check if pattern requires PRIMARY
     */
    public static function isPrimaryOnlyPattern(string $pattern): bool
    {
        $basePattern = self::getBasePattern($pattern);
        return in_array($basePattern, self::PRIMARY_ONLY_PATTERNS);
    }

    /**
     * FIX: DARK_MISUSE
     * Change any LIGHT-only pattern that has DARK back to LIGHT
     */
    private static function fixDarkMisuse(array $sections): array
    {
        foreach ($sections as $i => &$section) {
            $pattern = self::getSectionPattern($section);
            $context = self::getVisualContext($section);

            if ($context === 'DARK' && self::isLightOnlyPattern($pattern)) {
                $section = self::setVisualContext($section, 'LIGHT');
                $section['_autofix'][] = 'DARK_MISUSE:reverted_to_light';
                self::$appliedFixes[] = [
                    'rule' => 'DARK_MISUSE',
                    'action' => "Reverted {$pattern} from DARK to LIGHT (LIGHT-only pattern)",
                    'section_index' => $i,
                    'pattern' => $pattern
                ];
            }
        }

        return $sections;
    }

    /**
     * FIX: PRIMARY_COUNT_INVALID (HARDENED)
     * - PRIMARY must ONLY be on final_cta pattern
     * - If primary_sections === 0 and final_cta exists: set final_cta to PRIMARY
     * - If primary_sections === 0 and no final_cta: handled by fixMissingFinalCta
     * - If primary_sections > 1: keep only final_cta as PRIMARY, rest to LIGHT
     */
    private static function fixPrimaryCountInvalid(array $sections, array $issueCodes, array $quality): array
    {
        // Find final_cta section
        $finalCtaIndex = null;
        $primaryIndexes = [];

        foreach ($sections as $i => $section) {
            $pattern = self::getSectionPattern($section);
            $context = self::getVisualContext($section);

            if (self::isPrimaryOnlyPattern($pattern)) {
                $finalCtaIndex = $i;
            }

            if ($context === 'PRIMARY') {
                $primaryIndexes[] = $i;
            }
        }

        // Case 1: PRIMARY on non-final_cta patterns - demote to LIGHT
        foreach ($primaryIndexes as $idx) {
            $pattern = self::getSectionPattern($sections[$idx]);
            if (!self::isPrimaryOnlyPattern($pattern)) {
                $sections[$idx] = self::setVisualContext($sections[$idx], 'LIGHT');
                $sections[$idx]['_autofix'][] = 'PRIMARY_MISUSE:demoted_to_light';
                self::$appliedFixes[] = [
                    'rule' => 'PRIMARY_MISUSE',
                    'action' => "Demoted {$pattern} from PRIMARY to LIGHT (only final_cta can be PRIMARY)",
                    'section_index' => $idx,
                    'pattern' => $pattern
                ];
            }
        }

        // Case 2: final_cta exists but is not PRIMARY - set to PRIMARY
        if ($finalCtaIndex !== null) {
            $currentContext = self::getVisualContext($sections[$finalCtaIndex]);
            if ($currentContext !== 'PRIMARY') {
                $sections[$finalCtaIndex] = self::setVisualContext($sections[$finalCtaIndex], 'PRIMARY');
                $sections[$finalCtaIndex]['_autofix'][] = 'PRIMARY_COUNT_INVALID:set_primary';
                self::$appliedFixes[] = [
                    'rule' => 'PRIMARY_COUNT_INVALID',
                    'action' => 'Set final_cta to PRIMARY',
                    'section_index' => $finalCtaIndex
                ];
            }
        }

        return $sections;
    }

    /**
     * FIX: MISSING_FINAL_CTA
     * Add a final CTA section at the end if missing
     */
    private static function fixMissingFinalCta(array $sections, array $issueCodes): array
    {
        if (!in_array('MISSING_FINAL_CTA', $issueCodes) &&
            !in_array('missing_final_cta', $issueCodes)) {
            // Also check if any CTA-type section exists
            $hasFinalCta = false;
            foreach ($sections as $section) {
                $pattern = self::getSectionPattern($section);
                if (self::isPrimaryOnlyPattern($pattern)) {
                    $hasFinalCta = true;
                    break;
                }
            }
            if ($hasFinalCta) {
                return $sections;
            }
        }

        // Check if CTA already exists
        foreach ($sections as $section) {
            $pattern = self::getSectionPattern($section);
            if (self::isPrimaryOnlyPattern($pattern)) {
                return $sections; // Already has CTA
            }
        }

        // Create new CTA section
        $ctaSection = self::createFinalCtaSection(count($sections));
        $sections[] = $ctaSection;

        self::$appliedFixes[] = [
            'rule' => 'MISSING_FINAL_CTA',
            'action' => 'Added final CTA section with PRIMARY context',
            'section_index' => count($sections) - 1
        ];

        return $sections;
    }

    /**
     * Ensure trust_metrics sections have DARK context
     * This improves scoreContrast by +2 points
     */
    private static function ensureTrustMetricsDark(array $sections): array
    {
        foreach ($sections as $i => &$section) {
            $pattern = self::getSectionPattern($section);
            $basePattern = self::getBasePattern($pattern);

            // Only trust_metrics / trust / stats patterns should be DARK
            if (in_array($basePattern, ['trust_metrics', 'trust', 'stats'])) {
                $currentContext = self::getVisualContext($section);

                if ($currentContext !== 'DARK') {
                    $section = self::setVisualContext($section, 'DARK');
                    $section['_autofix'][] = 'TRUST_METRICS_DARK:applied';
                    self::$appliedFixes[] = [
                        'rule' => 'TRUST_METRICS_DARK',
                        'action' => "Set {$basePattern} to DARK context for contrast",
                        'section_index' => $i,
                        'pattern' => $pattern
                    ];
                }
            }
        }

        return $sections;
    }

    /**
     * Ensure final_cta is the last section
     */
    private static function ensureFinalCtaIsLast(array $sections): array
    {
        $finalCtaIndex = null;

        foreach ($sections as $i => $section) {
            $pattern = self::getSectionPattern($section);
            if (self::isPrimaryOnlyPattern($pattern)) {
                $finalCtaIndex = $i;
                break; // Take first final_cta found
            }
        }

        if ($finalCtaIndex !== null && $finalCtaIndex !== count($sections) - 1) {
            // Move final_cta to the end
            $finalCta = $sections[$finalCtaIndex];
            array_splice($sections, $finalCtaIndex, 1);
            $sections[] = $finalCta;

            self::$appliedFixes[] = [
                'rule' => 'FINAL_CTA_NOT_LAST',
                'action' => 'Moved final_cta to end of layout',
                'section_index' => count($sections) - 1
            ];
        }

        return $sections;
    }

    /**
     * FIX: DARK_OVERFLOW
     * Reduce DARK sections to max 2, keeping only trust_metrics and optionally hero
     */
    private static function fixDarkOverflow(array $sections): array
    {
        $darkSections = [];

        foreach ($sections as $i => $section) {
            if (self::getVisualContext($section) === 'DARK') {
                $darkSections[$i] = self::getSectionPattern($section);
            }
        }

        if (count($darkSections) <= 2) {
            return $sections;
        }

        // Prioritize: trust_metrics first, then hero
        $keepDark = [];
        $demoteToDark = [];

        foreach ($darkSections as $idx => $pattern) {
            $basePattern = self::getBasePattern($pattern);
            if ($basePattern === 'trust_metrics' || $basePattern === 'trust' || $basePattern === 'stats') {
                $keepDark[] = $idx;
            } elseif (strpos($basePattern, 'hero') === 0) {
                $demoteToDark[] = $idx; // Hero can be DARK but lower priority
            } else {
                $demoteToDark[] = $idx;
            }
        }

        // Keep max 2 DARK: prioritize trust_metrics, then hero
        $finalKeep = array_slice($keepDark, 0, 2);
        if (count($finalKeep) < 2 && !empty($demoteToDark)) {
            // Add hero if we have room
            foreach ($demoteToDark as $idx) {
                $pattern = self::getBasePattern($darkSections[$idx]);
                if (strpos($pattern, 'hero') === 0 && count($finalKeep) < 2) {
                    $finalKeep[] = $idx;
                }
            }
        }

        // Demote all others to LIGHT
        foreach ($darkSections as $idx => $pattern) {
            if (!in_array($idx, $finalKeep)) {
                $sections[$idx] = self::setVisualContext($sections[$idx], 'LIGHT');
                $sections[$idx]['_autofix'][] = 'DARK_OVERFLOW:demoted_to_light';
                self::$appliedFixes[] = [
                    'rule' => 'DARK_OVERFLOW',
                    'action' => "Demoted {$pattern} from DARK to LIGHT (max 2 DARK sections)",
                    'section_index' => $idx,
                    'pattern' => $pattern
                ];
            }
        }

        return $sections;
    }

    /**
     * FIX: LIGHT_MONOTONY (SAFE VERSION)
     * If >3 consecutive LIGHT sections, apply background alternation
     * WITHOUT changing visual_context to DARK
     *
     * ALWAYS runs regardless of issueCodes - prevents monotony proactively
     */
    private static function fixLightMonotonySafe(array $sections, array $issueCodes): array
    {
        // ALWAYS run this fix - don't wait for warning
        // This ensures background alternation is applied proactively

        $consecutiveLight = 0;
        $lightStreak = [];

        foreach ($sections as $i => $section) {
            $context = self::getVisualContext($section);

            if ($context === 'LIGHT') {
                $consecutiveLight++;
                $lightStreak[] = $i;
            } else {
                // Process streak if > 3 OR if we have any significant LIGHT streak (>= 2)
                // to ensure visual variety
                if ($consecutiveLight >= 3) {
                    $sections = self::applyLightAlternation($sections, $lightStreak);
                }
                $consecutiveLight = 0;
                $lightStreak = [];
            }
        }

        // Check final streak - also apply to final group
        if ($consecutiveLight >= 3) {
            $sections = self::applyLightAlternation($sections, $lightStreak);
        }

        return $sections;
    }

    /**
     * Apply light alternation (background_alt) instead of changing to DARK
     * Sets use_background_alt=true on every 2nd section in the streak
     *
     * FIXED: Now preserves AI-generated colors instead of hardcoding #F8FAFC
     * - If section already has a light background, keep it
     * - Only set background_type for renderer compatibility
     */
    private static function applyLightAlternation(array $sections, array $indexes): array
    {
        // Track global alternation state based on total LIGHT section count
        $lightCount = 0;

        foreach ($indexes as $pos => $i) {
            // Don't touch PRIMARY sections
            $context = self::getVisualContext($sections[$i]);
            if ($context === 'PRIMARY') {
                continue;
            }

            // Skip DARK sections (they provide contrast already)
            if ($context === 'DARK') {
                continue;
            }

            // Apply alternation to every 2nd LIGHT section (positions 1, 3, 5...)
            if ($lightCount % 2 === 1) {
                $pattern = self::getSectionPattern($sections[$i]);

                // Skip hero patterns (they should stay clean white)
                if (str_contains($pattern, 'hero')) {
                    $lightCount++;
                    continue;
                }

                // Apply background alternation (NOT changing to DARK)
                if (!isset($sections[$i]['attrs'])) {
                    $sections[$i]['attrs'] = [];
                }

                // FIXED: Preserve AI-generated background_color, only set type for renderer
                $existingBg = $sections[$i]['attrs']['background_color'] ?? null;

                $sections[$i]['attrs']['use_background_alt'] = true;
                $sections[$i]['attrs']['background_type'] = 'color';

                // Only set fallback color if AI didn't provide one
                if (empty($existingBg)) {
                    $sections[$i]['attrs']['background_color'] = '#f9fafb';
                }
                // Otherwise keep the AI-generated color!

                $sections[$i]['_autofix'][] = 'LIGHT_ALTERNATION:preserved';
                self::$appliedFixes[] = [
                    'rule' => 'LIGHT_ALTERNATION',
                    'action' => 'Applied alternation (kept original bg=' . ($existingBg ?? '#f9fafb') . ')',
                    'section_index' => $i
                ];
            }

            $lightCount++;
        }

        return $sections;
    }

    /**
     * FIX: LONG_PAGE
     * If sections > 10, merge adjacent sections with same pattern
     */
    private static function fixLongPage(array $sections, array $issueCodes): array
    {
        if (!in_array('LONG_PAGE', $issueCodes) &&
            !in_array('long_page', $issueCodes) &&
            !in_array('EXCESSIVE_SECTIONS', $issueCodes)) {
            return $sections;
        }

        if (count($sections) <= 10) {
            return $sections;
        }

        $merged = [];
        $i = 0;

        while ($i < count($sections)) {
            $current = $sections[$i];
            $currentPattern = self::getSectionPattern($current);

            // Look for mergeable next section
            if ($i + 1 < count($sections)) {
                $next = $sections[$i + 1];
                $nextPattern = self::getSectionPattern($next);

                // Merge if same pattern type (base pattern without variant)
                $currentBase = self::getBasePattern($currentPattern);
                $nextBase = self::getBasePattern($nextPattern);

                if ($currentBase === $nextBase && $currentBase !== 'hero' && $currentBase !== 'final' && $currentBase !== 'cta') {
                    // Merge sections
                    $current = self::mergeSections($current, $next);
                    $current['_autofix'][] = 'LONG_PAGE:merged_sections';
                    self::$appliedFixes[] = [
                        'rule' => 'LONG_PAGE',
                        'action' => "Merged sections with pattern: {$currentBase}",
                        'section_index' => count($merged)
                    ];
                    $i++; // Skip next since merged
                }
            }

            $merged[] = $current;
            $i++;

            // Stop merging if we're at 10 or less
            if (count($merged) + (count($sections) - $i) <= 10) {
                // Add remaining sections
                while ($i < count($sections)) {
                    $merged[] = $sections[$i];
                    $i++;
                }
                break;
            }
        }

        return $merged;
    }

    /**
     * FIX: GRID_DENSITY_LOW
     * If row has 1 column and >2 modules, split into 2 columns
     */
    private static function fixGridDensityLow(array $sections, array $issueCodes): array
    {
        if (!in_array('GRID_DENSITY_LOW', $issueCodes) &&
            !in_array('grid_density_low', $issueCodes) &&
            !in_array('LOW_DENSITY', $issueCodes)) {
            return $sections;
        }

        foreach ($sections as $sIdx => &$section) {
            $rows = $section['children'] ?? [];

            foreach ($rows as $rIdx => &$row) {
                $columns = $row['children'] ?? [];

                // Single column with many modules
                if (count($columns) === 1) {
                    $modules = $columns[0]['children'] ?? [];

                    if (count($modules) > 2) {
                        // Split into 2 columns
                        $row = self::splitColumnIntoTwo($row, $modules);
                        $row['_autofix'][] = 'GRID_DENSITY_LOW:split_column';
                        $section['_autofix'][] = 'GRID_DENSITY_LOW:restructured_row';

                        self::$appliedFixes[] = [
                            'rule' => 'GRID_DENSITY_LOW',
                            'action' => 'Split single column into 2 columns',
                            'section_index' => $sIdx,
                            'row_index' => $rIdx
                        ];
                    }
                }
            }

            $section['children'] = $rows;
        }

        return $sections;
    }

    // =========================================================================
    // Helper Methods
    // =========================================================================

    /**
     * Get section pattern name
     */
    private static function getSectionPattern(array $section): string
    {
        return $section['_pattern']
            ?? $section['attrs']['_pattern']
            ?? $section['attrs']['pattern']
            ?? 'unknown';
    }

    /**
     * Get visual context from section
     */
    private static function getVisualContext(array $section): string
    {
        return strtoupper(
            $section['_visual_context']
            ?? $section['attrs']['visual_context']
            ?? $section['attrs']['_visual_context']
            ?? 'LIGHT'
        );
    }

    /**
     * Set visual context on section
     */
    private static function setVisualContext(array $section, string $context): array
    {
        $section['_visual_context'] = $context;
        if (!isset($section['attrs'])) {
            $section['attrs'] = [];
        }
        $section['attrs']['visual_context'] = $context;
        $section['attrs']['_visual_context'] = $context;

        // Update background color based on context
        $bgColors = [
            'LIGHT' => '#ffffff',
            'DARK' => '#1e293b',
            'PRIMARY' => '#3b82f6'
        ];

        if (isset($bgColors[$context])) {
            $section['attrs']['background_color'] = $bgColors[$context];

            // Update text colors for contrast
            if ($context === 'DARK' || $context === 'PRIMARY') {
                self::updateTextColors($section, '#ffffff');
            } else {
                self::updateTextColors($section, '#1f2937');
            }
        }

        return $section;
    }

    /**
     * Update text colors in section modules for contrast
     */
    private static function updateTextColors(array &$section, string $color): void
    {
        $rows = $section['children'] ?? [];
        foreach ($rows as &$row) {
            $columns = $row['children'] ?? [];
            foreach ($columns as &$column) {
                $modules = $column['children'] ?? [];
                foreach ($modules as &$module) {
                    $type = $module['type'] ?? '';
                    // Apply text color to ALL non-structure modules (not just heading/text/blurb)
                    $structureTypes = ['section', 'row', 'column'];
                    if (!empty($type) && !in_array($type, $structureTypes)) {
                        if (!isset($module['attrs'])) {
                            $module['attrs'] = [];
                        }
                        $module['attrs']['text_color'] = $color;
                    }
                }
                $column['children'] = $modules;
            }
            $row['children'] = $columns;
        }
        $section['children'] = $rows;
    }

    /**
     * Get base pattern name (without variant suffix)
     */
    private static function getBasePattern(string $pattern): string
    {
        // Remove variant suffixes like _v1, _alt, _simple, _centered, _split, _asymmetric
        $base = preg_replace('/_(v\d+|alt|simple|centered|split|asymmetric|expandable|tiered|gateway|spotlight|narrative|disclosure)$/i', '', $pattern);
        return $base;
    }

    /**
     * Merge two sections (combine their rows)
     */
    private static function mergeSections(array $section1, array $section2): array
    {
        $rows1 = $section1['children'] ?? [];
        $rows2 = $section2['children'] ?? [];

        // Merge rows from section2 into section1
        foreach ($rows2 as $row) {
            $rows1[] = $row;
        }

        $section1['children'] = $rows1;
        return $section1;
    }

    /**
     * Split single column into two columns with modules distributed
     */
    private static function splitColumnIntoTwo(array $row, array $modules): array
    {
        $half = (int)ceil(count($modules) / 2);
        $leftModules = array_slice($modules, 0, $half);
        $rightModules = array_slice($modules, $half);

        $row['children'] = [
            [
                'id' => 'column_' . self::generateId(),
                'type' => 'column',
                'attrs' => [],
                'children' => $leftModules
            ],
            [
                'id' => 'column_' . self::generateId(),
                'type' => 'column',
                'attrs' => [],
                'children' => $rightModules
            ]
        ];

        // Update row columns attribute
        if (!isset($row['attrs'])) {
            $row['attrs'] = [];
        }
        $row['attrs']['columns'] = '1_2_1_2';

        return $row;
    }

    /**
     * Create a final CTA section
     */
    private static function createFinalCtaSection(int $index): array
    {
        $sectionId = 'section_autofix_cta_' . self::generateId();
        $rowId = 'row_' . self::generateId();
        $colId = 'column_' . self::generateId();
        $headingId = 'heading_' . self::generateId();
        $textId = 'text_' . self::generateId();
        $buttonId = 'button_' . self::generateId();

        return [
            'id' => $sectionId,
            'type' => 'section',
            '_pattern' => 'final_cta',
            '_visual_context' => 'PRIMARY',
            '_autofix' => ['MISSING_FINAL_CTA:created_section'],
            'attrs' => [
                'background_color' => '#3b82f6',
                'visual_context' => 'PRIMARY',
                '_visual_context' => 'PRIMARY',
                '_pattern' => 'final_cta',
                'padding' => ['top' => 80, 'right' => 0, 'bottom' => 80, 'left' => 0]
            ],
            'children' => [
                [
                    'id' => $rowId,
                    'type' => 'row',
                    'attrs' => ['columns' => '1'],
                    'children' => [
                        [
                            'id' => $colId,
                            'type' => 'column',
                            'attrs' => [],
                            'children' => [
                                [
                                    'id' => $headingId,
                                    'type' => 'heading',
                                    'attrs' => [
                                        'text' => 'Ready to Get Started?',
                                        'level' => 'h2',
                                        'text_color' => '#ffffff',
                                        'text_align' => 'center',
                                        'font_size' => 36
                                    ],
                                    'children' => []
                                ],
                                [
                                    'id' => $textId,
                                    'type' => 'text',
                                    'attrs' => [
                                        'content' => 'Take the next step and discover how we can help you achieve your goals.',
                                        'text_color' => 'rgba(255,255,255,0.9)',
                                        'text_align' => 'center',
                                        'font_size' => 18
                                    ],
                                    'children' => []
                                ],
                                [
                                    'id' => $buttonId,
                                    'type' => 'button',
                                    'attrs' => [
                                        'text' => 'Get Started Today',
                                        'link_url' => '#contact',
                                        'button_bg_color' => '#ffffff',
                                        'button_text_color' => '#3b82f6',
                                        'alignment' => 'center',
                                        'button_border_radius' => ['top_left' => 8, 'top_right' => 8, 'bottom_right' => 8, 'bottom_left' => 8]
                                    ],
                                    'children' => []
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }

    /**
     * Generate unique ID
     */
    private static function generateId(): string
    {
        return substr(md5(uniqid((string)mt_rand(), true)), 0, 8);
    }

    // =========================================================================
    // Stage 12: Visual Intent Engine
    // =========================================================================

    /**
     * Apply visual_intent to all sections based on pattern mapping
     * This is deterministic: pattern -> intent (no AI guessing)
     */
    private static function applyVisualIntent(array $sections): array
    {
        foreach ($sections as $i => &$section) {
            $pattern = self::getSectionPattern($section);
            $intent = self::getVisualIntentForPattern($pattern);

            // Set visual_intent on section
            $section['_visual_intent'] = $intent;
            if (!isset($section['attrs'])) {
                $section['attrs'] = [];
            }
            $section['attrs']['visual_intent'] = $intent;
            $section['attrs']['_visual_intent'] = $intent;

            // Log if we assigned intent
            if ($intent !== 'NEUTRAL') {
                self::$appliedFixes[] = [
                    'rule' => 'VISUAL_INTENT',
                    'action' => "Assigned {$intent} intent to {$pattern}",
                    'section_index' => $i,
                    'pattern' => $pattern,
                    'intent' => $intent
                ];
            }
        }

        return $sections;
    }

    /**
     * Get visual intent for a pattern
     * Returns DOMINANT, EMPHASIS, NEUTRAL, or SOFT
     */
    public static function getVisualIntentForPattern(string $pattern): string
    {
        $basePattern = self::getBasePattern($pattern);

        // Direct match first
        if (isset(self::PATTERN_INTENT_MAP[$pattern])) {
            return self::PATTERN_INTENT_MAP[$pattern];
        }

        // Base pattern match
        if (isset(self::PATTERN_INTENT_MAP[$basePattern])) {
            return self::PATTERN_INTENT_MAP[$basePattern];
        }

        // Prefix matching for hero* and cta*
        if (strpos($pattern, 'hero') === 0) {
            return 'DOMINANT';
        }
        if (strpos($pattern, 'final_cta') === 0 || $pattern === 'cta') {
            return 'DOMINANT';
        }
        if (strpos($pattern, 'pricing') === 0) {
            return 'EMPHASIS';
        }
        if (strpos($pattern, 'trust') === 0 || strpos($pattern, 'stats') === 0) {
            return 'EMPHASIS';
        }
        if (strpos($pattern, 'faq') === 0 || strpos($pattern, 'footer') === 0) {
            return 'SOFT';
        }

        // Default to NEUTRAL
        return 'NEUTRAL';
    }

    private static function applyVisualDensity(array $sections): array
    {
        foreach ($sections as $i => &$section) {
            if (isset($section['_visual_density']) || isset($section['attrs']['visual_density'])) {
                continue;
            }

            $pattern = self::getSectionPattern($section);
            $density = self::getVisualDensityForPattern($pattern);

            $section['_visual_density'] = $density;
            if (!isset($section['attrs'])) {
                $section['attrs'] = [];
            }
            $section['attrs']['visual_density'] = $density;

            if ($density !== 'NORMAL') {
                self::$appliedFixes[] = [
                    'rule' => 'VISUAL_DENSITY',
                    'action' => "Assigned {$density} density to {$pattern}",
                    'section_index' => $i,
                    'pattern' => $pattern,
                    'density' => $density
                ];
            }
        }

        return $sections;
    }

    public static function getVisualDensityForPattern(string $pattern): string
    {
        $basePattern = self::getBasePattern($pattern);

        if (isset(self::PATTERN_DENSITY_MAP[$pattern])) {
            return self::PATTERN_DENSITY_MAP[$pattern];
        }

        if (isset(self::PATTERN_DENSITY_MAP[$basePattern])) {
            return self::PATTERN_DENSITY_MAP[$basePattern];
        }

        if (strpos($pattern, 'hero') === 0) {
            return 'DENSE';
        }
        if (strpos($pattern, 'features') === 0 || strpos($pattern, 'grid') === 0) {
            return 'DENSE';
        }
        if (strpos($pattern, 'pricing') === 0) {
            return 'DENSE';
        }
        if (strpos($pattern, 'faq') === 0 || strpos($pattern, 'contact') === 0) {
            return 'SPARSE';
        }
        if (strpos($pattern, 'breathing') === 0 || strpos($pattern, 'visual_bridge') === 0) {
            return 'SPARSE';
        }

        return 'NORMAL';
    }

    private static function applyVisualRhythm(array $sections): array
    {
        $count = count($sections);
        $prevDensity = null;

        foreach ($sections as $i => &$section) {
            if (!isset($section['attrs'])) {
                $section['attrs'] = [];
            }

            $pattern = self::getSectionPattern($section);
            $density = $section['_visual_density'] ?? $section['attrs']['visual_density'] ?? 'NORMAL';
            $intent = $section['_visual_intent'] ?? $section['attrs']['visual_intent'] ?? 'NEUTRAL';

            if (self::isPrimaryOnlyPattern($pattern)) {
                if (!isset($section['attrs']['before_spacing'])) {
                    $section['attrs']['before_spacing'] = '2xl';
                    self::$appliedFixes[] = [
                        'rule' => 'VISUAL_RHYTHM',
                        'action' => 'FINAL_CTA climax: before_spacing=2xl',
                        'section_index' => $i
                    ];
                }
                if (!isset($section['attrs']['after_spacing'])) {
                    $section['attrs']['after_spacing'] = '2xl';
                    self::$appliedFixes[] = [
                        'rule' => 'VISUAL_RHYTHM',
                        'action' => 'FINAL_CTA climax: after_spacing=2xl',
                        'section_index' => $i
                    ];
                }
                $prevDensity = $density;
                continue;
            }

            if ($intent === 'DOMINANT') {
                if (!isset($section['attrs']['before_spacing'])) {
                    $section['attrs']['before_spacing'] = 'xl';
                    self::$appliedFixes[] = [
                        'rule' => 'VISUAL_RHYTHM',
                        'action' => 'DOMINANT breathe: before_spacing=xl',
                        'section_index' => $i
                    ];
                }
                if (!isset($section['attrs']['after_spacing'])) {
                    $section['attrs']['after_spacing'] = 'xl';
                    self::$appliedFixes[] = [
                        'rule' => 'VISUAL_RHYTHM',
                        'action' => 'DOMINANT breathe: after_spacing=xl',
                        'section_index' => $i
                    ];
                }
            }

            if ($density === 'DENSE' && $prevDensity === 'DENSE') {
                if (!isset($section['attrs']['before_spacing'])) {
                    $section['attrs']['before_spacing'] = 'xl';
                    self::$appliedFixes[] = [
                        'rule' => 'VISUAL_RHYTHM',
                        'action' => 'DENSE chain break: before_spacing=xl',
                        'section_index' => $i
                    ];
                }
            }

            if ($density === 'SPARSE') {
                $current = $section['attrs']['before_spacing'] ?? null;
                if ($current === null || self::spacingToInt($current) < self::spacingToInt('md')) {
                    $section['attrs']['before_spacing'] = 'md';
                    self::$appliedFixes[] = [
                        'rule' => 'VISUAL_RHYTHM',
                        'action' => 'SPARSE minimum: before_spacing=md',
                        'section_index' => $i
                    ];
                }
                $currentAfter = $section['attrs']['after_spacing'] ?? null;
                if ($currentAfter === null || self::spacingToInt($currentAfter) < self::spacingToInt('md')) {
                    $section['attrs']['after_spacing'] = 'md';
                    self::$appliedFixes[] = [
                        'rule' => 'VISUAL_RHYTHM',
                        'action' => 'SPARSE minimum: after_spacing=md',
                        'section_index' => $i
                    ];
                }
            }

            $prevDensity = $density;
        }

        return $sections;
    }

    private static function spacingToInt(string $spacing): int
    {
        $map = ['sm' => 1, 'md' => 2, 'lg' => 3, 'xl' => 4, '2xl' => 5];
        return $map[$spacing] ?? 0;
    }

    private static function applyVisualScale(array $sections, array $quality): array
    {
        $count = count($sections);
        $score = $quality['score'] ?? 0;
        $confidence = $quality['confidence'] ?? 50;

        $heroIndex = null;
        $ctaIndex = null;
        $dominantCount = 0;
        $hasTrustAfterHero = false;

        foreach ($sections as $i => $section) {
            $pattern = self::getSectionPattern($section);
            $intent = $section['_visual_intent'] ?? $section['attrs']['visual_intent'] ?? 'NEUTRAL';

            if (strpos($pattern, 'hero') === 0 && $heroIndex === null) {
                $heroIndex = $i;
            }
            if (self::isPrimaryOnlyPattern($pattern)) {
                $ctaIndex = $i;
            }
            if ($intent === 'DOMINANT') {
                $dominantCount++;
            }
        }

        if ($heroIndex !== null && $heroIndex + 1 < $count) {
            $nextPattern = self::getSectionPattern($sections[$heroIndex + 1]);
            if (strpos($nextPattern, 'trust') === 0 || $nextPattern === 'stats') {
                $hasTrustAfterHero = true;
            }
        }

        foreach ($sections as $i => &$section) {
            if (isset($section['_visual_scale']) || isset($section['attrs']['visual_scale'])) {
                continue;
            }

            $pattern = self::getSectionPattern($section);
            $intent = $section['_visual_intent'] ?? $section['attrs']['visual_intent'] ?? 'NEUTRAL';
            $scale = self::getDefaultScaleForIntent($intent);

            if ($i === $heroIndex) {
                if ($dominantCount === 1 || $hasTrustAfterHero || strpos($pattern, 'hero_split') !== false) {
                    $scale = 'XL';
                } elseif ($count > 8) {
                    $scale = 'LG';
                } elseif ($count <= 6) {
                    $scale = 'XL';
                } else {
                    $scale = 'LG';
                }
                self::$appliedFixes[] = [
                    'rule' => 'VISUAL_SCALE',
                    'action' => "HERO adaptive scale: {$scale}",
                    'section_index' => $i
                ];
            } elseif ($i === $ctaIndex) {
                if ($score >= 18 || $confidence >= 70) {
                    $scale = 'XL';
                } elseif ($count > 9) {
                    $scale = 'XL';
                } else {
                    $scale = 'LG';
                }
                self::$appliedFixes[] = [
                    'rule' => 'VISUAL_SCALE',
                    'action' => "FINAL_CTA climax scale: {$scale}",
                    'section_index' => $i
                ];
            } elseif ($intent === 'DOMINANT') {
                $scale = 'MD';
                self::$appliedFixes[] = [
                    'rule' => 'VISUAL_SCALE',
                    'action' => "Other DOMINANT capped: {$scale}",
                    'section_index' => $i
                ];
            } elseif ($intent === 'EMPHASIS') {
                if ($ctaIndex !== null && $i === $ctaIndex - 1) {
                    $scale = 'LG';
                    self::$appliedFixes[] = [
                        'rule' => 'VISUAL_SCALE',
                        'action' => "EMPHASIS pre-CTA boost: {$scale}",
                        'section_index' => $i
                    ];
                } else {
                    $scale = 'MD';
                }
            }

            $section['_visual_scale'] = $scale;
            if (!isset($section['attrs'])) {
                $section['attrs'] = [];
            }
            $section['attrs']['visual_scale'] = $scale;
        }

        return $sections;
    }

    public static function getDefaultScaleForIntent(string $intent): string
    {
        switch ($intent) {
            case 'DOMINANT':
                return 'LG';
            case 'EMPHASIS':
                return 'MD';
            case 'SOFT':
                return 'SM';
            default:
                return 'MD';
        }
    }

    private static function applyTypographyIntent(array $sections): array
    {
        foreach ($sections as $i => &$section) {
            if (isset($section['attrs']['typography_scale']) && isset($section['attrs']['text_emphasis'])) {
                continue;
            }

            if (!isset($section['attrs'])) {
                $section['attrs'] = [];
            }

            $pattern = self::getSectionPattern($section);
            $intent = $section['_visual_intent'] ?? $section['attrs']['visual_intent'] ?? 'NEUTRAL';
            $scale = $section['_visual_scale'] ?? $section['attrs']['visual_scale'] ?? 'MD';

            $typoScale = $scale;
            if ((strpos($pattern, 'hero') === 0 || self::isPrimaryOnlyPattern($pattern)) && $scale === 'XL') {
                $typoScale = 'XL';
            }

            $textEmphasis = 'normal';
            if ($intent === 'DOMINANT' || $intent === 'EMPHASIS') {
                $textEmphasis = 'strong';
            } elseif ($intent === 'SOFT') {
                $textEmphasis = 'soft';
            }

            if (!isset($section['attrs']['typography_scale'])) {
                $section['attrs']['typography_scale'] = $typoScale;
                $section['_typography_scale'] = $typoScale;

                if ($typoScale !== 'MD') {
                    self::$appliedFixes[] = [
                        'rule' => 'TYPOGRAPHY_INTENT',
                        'action' => "Typography scale: {$typoScale}",
                        'section_index' => $i
                    ];
                }
            }

            if (!isset($section['attrs']['text_emphasis'])) {
                $section['attrs']['text_emphasis'] = $textEmphasis;
                $section['_text_emphasis'] = $textEmphasis;

                if ($textEmphasis !== 'normal') {
                    self::$appliedFixes[] = [
                        'rule' => 'TYPOGRAPHY_INTENT',
                        'action' => "Text emphasis: {$textEmphasis}",
                        'section_index' => $i
                    ];
                }
            }
        }

        return $sections;
    }

    private static function applyEmotionalFlow(array $sections): array
    {
        $count = count($sections);
        $consecutiveHigh = 0;

        foreach ($sections as $i => &$section) {
            if (isset($section['attrs']['emotional_tone']) && isset($section['attrs']['attention_level'])) {
                continue;
            }

            if (!isset($section['attrs'])) {
                $section['attrs'] = [];
            }

            $pattern = self::getSectionPattern($section);
            $emotional = self::getEmotionalForPattern($pattern);

            $tone = $emotional['tone'];
            $attention = $emotional['attention'];

            if ($attention === 'high') {
                $consecutiveHigh++;
                if ($consecutiveHigh > 2) {
                    $attention = 'medium';
                    self::$appliedFixes[] = [
                        'rule' => 'EMOTIONAL_FLOW',
                        'action' => 'Attention capped to prevent overload',
                        'section_index' => $i
                    ];
                }
            } else {
                $consecutiveHigh = 0;
            }

            $section['_emotional_tone'] = $tone;
            $section['_attention_level'] = $attention;
            $section['attrs']['emotional_tone'] = $tone;
            $section['attrs']['attention_level'] = $attention;

            if ($tone !== 'focus' || $attention !== 'medium') {
                self::$appliedFixes[] = [
                    'rule' => 'EMOTIONAL_FLOW',
                    'action' => "Emotional: {$tone}/{$attention}",
                    'section_index' => $i
                ];
            }
        }

        return $sections;
    }

    public static function getEmotionalForPattern(string $pattern): array
    {
        $basePattern = self::getBasePattern($pattern);

        if (isset(self::PATTERN_EMOTIONAL_MAP[$pattern])) {
            return self::PATTERN_EMOTIONAL_MAP[$pattern];
        }

        if (isset(self::PATTERN_EMOTIONAL_MAP[$basePattern])) {
            return self::PATTERN_EMOTIONAL_MAP[$basePattern];
        }

        if (strpos($pattern, 'hero') === 0) {
            return ['tone' => 'focus', 'attention' => 'high'];
        }
        if (strpos($pattern, 'final_cta') === 0 || $pattern === 'cta') {
            return ['tone' => 'urgency', 'attention' => 'high'];
        }
        if (strpos($pattern, 'trust') === 0 || strpos($pattern, 'testimonial') === 0) {
            return ['tone' => 'trust', 'attention' => 'medium'];
        }
        if (strpos($pattern, 'faq') === 0 || strpos($pattern, 'breathing') === 0) {
            return ['tone' => 'calm', 'attention' => 'low'];
        }

        return ['tone' => 'focus', 'attention' => 'medium'];
    }

    // ============================================================
    // Stage 17: NARRATIVE FLOW ENGINE
    // ============================================================

    /**
     * Stage 17: Apply narrative roles to all sections
     */
    private static function applyNarrativeRoles(array $sections): array
    {
        foreach ($sections as $i => &$section) {
            $pattern = self::getSectionPattern($section);
            $role = self::getNarrativeRoleForPattern($pattern);

            $section['attrs']['narrative_role'] = $role;
            $section['_narrative_role'] = $role;

            self::$appliedFixes[] = [
                'rule' => 'NARRATIVE_ROLE',
                'action' => "Assigned role: {$role}",
                'section_index' => $i,
                'pattern' => $pattern
            ];
        }

        return $sections;
    }

    /**
     * Get narrative role for a pattern
     */
    public static function getNarrativeRoleForPattern(string $pattern): string
    {
        $basePattern = self::getBasePattern($pattern);

        // Direct match
        if (isset(self::PATTERN_NARRATIVE_ROLE_MAP[$pattern])) {
            return self::PATTERN_NARRATIVE_ROLE_MAP[$pattern];
        }

        // Base pattern match
        if (isset(self::PATTERN_NARRATIVE_ROLE_MAP[$basePattern])) {
            return self::PATTERN_NARRATIVE_ROLE_MAP[$basePattern];
        }

        // Wildcard matching
        if (strpos($pattern, 'hero') === 0) {
            return 'HOOK';
        }
        if (strpos($pattern, 'problem') === 0 || strpos($pattern, 'challenge') === 0) {
            return 'PROBLEM';
        }
        if (strpos($pattern, 'feature') === 0 || strpos($pattern, 'benefit') === 0 || strpos($pattern, 'service') === 0) {
            return 'PROMISE';
        }
        if (strpos($pattern, 'trust') === 0 || strpos($pattern, 'testimonial') === 0 || strpos($pattern, 'stats') === 0) {
            return 'PROOF';
        }
        if (strpos($pattern, 'pricing') === 0 || strpos($pattern, 'faq') === 0) {
            return 'DETAILS';
        }
        if (strpos($pattern, 'contact') === 0 || strpos($pattern, 'breathing') === 0) {
            return 'RELIEF';
        }
        if (strpos($pattern, 'final_cta') === 0 || $pattern === 'cta') {
            return 'RESOLUTION';
        }

        // Default to PROMISE for unknown patterns (most common)
        return 'PROMISE';
    }

    /**
     * Validate narrative flow and return analysis
     *
     * @return array [
     *   'signature' => string (e.g., 'H-PR-PF-D-R-U'),
     *   'missing_roles' => array,
     *   'out_of_order_roles' => array,
     *   'score' => int (0-100),
     *   'issues' => array
     * ]
     */
    public static function validateNarrativeFlow(array $sections): array
    {
        $roles = [];
        $rolePositions = [];

        // Collect roles in order
        foreach ($sections as $i => $section) {
            $role = $section['_narrative_role'] ?? $section['attrs']['narrative_role'] ?? null;
            if ($role) {
                $roles[] = $role;
                if (!isset($rolePositions[$role])) {
                    $rolePositions[$role] = [];
                }
                $rolePositions[$role][] = $i;
            }
        }

        // Generate signature
        $signatureMap = [
            'HOOK' => 'H',
            'PROBLEM' => 'PB',
            'PROMISE' => 'PR',
            'PROOF' => 'PF',
            'DETAILS' => 'D',
            'RELIEF' => 'RL',
            'RESOLUTION' => 'RS'
        ];

        $signatureParts = [];
        $lastRole = null;
        foreach ($roles as $role) {
            // Collapse consecutive same roles
            if ($role !== $lastRole) {
                $signatureParts[] = $signatureMap[$role] ?? '?';
                $lastRole = $role;
            }
        }
        $signature = implode('-', $signatureParts);

        // Check missing roles (HOOK, PROMISE, PROOF, RESOLUTION are essential)
        $essentialRoles = ['HOOK', 'PROMISE', 'PROOF', 'RESOLUTION'];
        $missingRoles = [];
        foreach ($essentialRoles as $essential) {
            if (!isset($rolePositions[$essential])) {
                $missingRoles[] = $essential;
            }
        }

        // Check order violations
        $outOfOrder = [];
        $issues = [];

        // Rule: RESOLUTION should be last (or near last)
        if (isset($rolePositions['RESOLUTION'])) {
            $resPos = min($rolePositions['RESOLUTION']);
            $totalSections = count($sections);
            if ($resPos < $totalSections - 2) {
                $outOfOrder[] = ['role' => 'RESOLUTION', 'position' => $resPos, 'issue' => 'RESOLUTION_TOO_EARLY'];
                $issues[] = 'CTA_NOT_LAST';
            }
        }

        // Rule: PROOF should come before RESOLUTION
        if (isset($rolePositions['PROOF']) && isset($rolePositions['RESOLUTION'])) {
            $proofMax = max($rolePositions['PROOF']);
            $resMin = min($rolePositions['RESOLUTION']);
            if ($proofMax > $resMin) {
                $outOfOrder[] = ['role' => 'PROOF', 'position' => $proofMax, 'issue' => 'PROOF_AFTER_CTA'];
            }
        }

        // Rule: PROMISE should come before PROOF
        if (isset($rolePositions['PROMISE']) && isset($rolePositions['PROOF'])) {
            $promiseMin = min($rolePositions['PROMISE']);
            $proofMin = min($rolePositions['PROOF']);
            if ($promiseMin > $proofMin) {
                $outOfOrder[] = ['role' => 'PROMISE', 'position' => $promiseMin, 'issue' => 'PROOF_BEFORE_PROMISE'];
                $issues[] = 'PROOF_BEFORE_PROMISE';
            }
        }

        // Rule: CTA before PROMISE is a HARD FAIL
        if (isset($rolePositions['RESOLUTION']) && isset($rolePositions['PROMISE'])) {
            $resMin = min($rolePositions['RESOLUTION']);
            $promiseMax = max($rolePositions['PROMISE']);
            if ($resMin < $promiseMax) {
                $issues[] = 'CTA_BEFORE_PROMISE';
            }
        }

        // Rule: HOOK should be first
        if (isset($rolePositions['HOOK'])) {
            $hookMin = min($rolePositions['HOOK']);
            if ($hookMin > 0) {
                $outOfOrder[] = ['role' => 'HOOK', 'position' => $hookMin, 'issue' => 'HOOK_NOT_FIRST'];
            }
        }

        // Calculate score
        $score = 100;

        // Missing essential roles: -20 each
        $score -= count($missingRoles) * 20;

        // Out of order: -10 each
        $score -= count($outOfOrder) * 10;

        // CTA_BEFORE_PROMISE is critical: -30
        if (in_array('CTA_BEFORE_PROMISE', $issues)) {
            $score -= 30;
        }

        // Bonus for having PROOF: +10
        if (isset($rolePositions['PROOF']) && count($rolePositions['PROOF']) >= 2) {
            $score += 10;
        }

        // Bonus for having RELIEF before RESOLUTION: +5
        if (isset($rolePositions['RELIEF']) && isset($rolePositions['RESOLUTION'])) {
            $reliefMax = max($rolePositions['RELIEF']);
            $resMin = min($rolePositions['RESOLUTION']);
            if ($reliefMax < $resMin) {
                $score += 5;
            }
        }

        $score = max(0, min(100, $score));

        return [
            'signature' => $signature,
            'missing_roles' => $missingRoles,
            'out_of_order_roles' => $outOfOrder,
            'score' => $score,
            'issues' => $issues,
            'has_hook' => isset($rolePositions['HOOK']),
            'has_promise' => isset($rolePositions['PROMISE']),
            'has_proof' => isset($rolePositions['PROOF']),
            'has_resolution' => isset($rolePositions['RESOLUTION']),
            'has_relief' => isset($rolePositions['RELIEF']),
            'role_positions' => $rolePositions,
            'total_roles' => count($roles)
        ];
    }

    /**
     * Stage 17: Auto-fix narrative flow issues
     * Only safe fixes - no structure changes
     */
    private static function fixNarrativeFlow(array $sections): array
    {
        $validation = self::validateNarrativeFlow($sections);

        // Check for missing PROOF before DETAILS
        if (in_array('PROOF', $validation['missing_roles']) && $validation['has_promise']) {
            // Find first DETAILS or RESOLUTION position
            $insertPos = null;
            foreach ($sections as $i => $section) {
                $role = $section['_narrative_role'] ?? '';
                if (in_array($role, ['DETAILS', 'RESOLUTION'])) {
                    $insertPos = $i;
                    break;
                }
            }

            if ($insertPos !== null && $insertPos > 0) {
                // Mark that we SHOULD have PROOF here (can't insert sections in autofix)
                $sections[$insertPos - 1]['_narrative_needs_proof_after'] = true;
                self::$appliedFixes[] = [
                    'rule' => 'NARRATIVE_MISSING_PROOF',
                    'action' => 'Flagged: needs PROOF section before DETAILS',
                    'section_index' => $insertPos
                ];
            }
        }

        // Check for missing RELIEF before RESOLUTION
        if (!$validation['has_relief'] && $validation['has_resolution']) {
            // Find RESOLUTION position
            foreach ($sections as $i => $section) {
                $role = $section['_narrative_role'] ?? '';
                if ($role === 'RESOLUTION' && $i > 0) {
                    // Mark that previous section should provide relief
                    $sections[$i - 1]['_narrative_needs_relief'] = true;
                    self::$appliedFixes[] = [
                        'rule' => 'NARRATIVE_MISSING_RELIEF',
                        'action' => 'Flagged: needs RELIEF before RESOLUTION',
                        'section_index' => $i
                    ];
                    break;
                }
            }
        }

        // CTA_BEFORE_PROMISE is a HARD FAIL - just flag it
        if (in_array('CTA_BEFORE_PROMISE', $validation['issues'])) {
            foreach ($sections as $i => &$section) {
                $role = $section['_narrative_role'] ?? '';
                if ($role === 'RESOLUTION') {
                    $section['_narrative_hard_fail'] = 'CTA_BEFORE_PROMISE';
                    self::$appliedFixes[] = [
                        'rule' => 'NARRATIVE_HARD_FAIL',
                        'action' => 'CRITICAL: CTA before PROMISE - broken story',
                        'section_index' => $i
                    ];
                }
            }
        }

        return $sections;
    }

    /**
     * Get narrative validation result (public for generator)
     */
    public static function getNarrativeValidation(array $sections): array
    {
        return self::validateNarrativeFlow($sections);
    }

    // =========================================================================
    // STAGE 18: NARRATIVE AUTO-CORRECTION (SAFE & DETERMINISTIC)
    // =========================================================================

    /**
     * Stage 18 counters for metrics
     */
    private static int $narrativePlaceholdersCount = 0;
    private static int $narrativeSwapsCount = 0;
    private static bool $narrativeAutofixBlocked = false;

    /**
     * Stage 18: Apply safe narrative auto-fixes
     *
     * Rules:
     * - NO content changes
     * - NO aggressive reordering
     * - ONLY: insert placeholders, swap adjacent sections
     * - Preserve original layout if score >= 60
     *
     * @param array $sections Layout sections
     * @param array $quality Quality metrics from previous stages
     * @return array Fixed sections
     */
    private static function applyNarrativeAutoFix(array $sections, array $quality): array
    {
        // Reset counters
        self::$narrativePlaceholdersCount = 0;
        self::$narrativeSwapsCount = 0;
        self::$narrativeAutofixBlocked = false;

        // Validate current narrative state
        $validation = self::validateNarrativeFlow($sections);
        $score = $validation['score'] ?? 100;

        // Rule: If score >= 60, preserve original layout (minimal fixes only)
        if ($score >= 60) {
            self::$appliedFixes[] = [
                'rule' => 'NR_SCORE_OK',
                'action' => "Narrative score {$score} >= 60, minimal fixes only",
                'stage' => 18
            ];
            return $sections;
        }

        // Rule: If score < 40, BLOCK auto-fix (too broken to safely fix)
        if ($score < 40) {
            self::$narrativeAutofixBlocked = true;
            foreach ($sections as &$section) {
                $section['_autofix_blocked'] = true;
            }
            self::$appliedFixes[] = [
                'rule' => 'NR_AUTOFIX_BLOCKED',
                'action' => "Narrative score {$score} < 40, auto-fix blocked (manual intervention needed)",
                'stage' => 18
            ];
            return $sections;
        }

        // Score 40-59: Apply safe fixes
        $rolePositions = $validation['role_positions'] ?? [];
        $missingRoles = $validation['missing_roles'] ?? [];
        $issues = $validation['issues'] ?? [];

        // =====================================================================
        // A) FIX: CTA_BEFORE_PROMISE (SAFE SWAP)
        // =====================================================================
        if (in_array('CTA_BEFORE_PROMISE', $issues)) {
            $sections = self::fixCtaBeforePromise($sections, $rolePositions);
        }

        // =====================================================================
        // B) FIX: PROOF_BEFORE_PROMISE (SAFE SWAP)
        // =====================================================================
        if (in_array('PROOF_BEFORE_PROMISE', $issues)) {
            $sections = self::fixProofBeforePromise($sections, $rolePositions);
        }

        // =====================================================================
        // C) FIX: MISSING PROOF (INSERT PLACEHOLDER)
        // =====================================================================
        if (in_array('PROOF', $missingRoles) && self::canInsertNarrativeRole('PROOF')) {
            $sections = self::insertProofPlaceholder($sections, $rolePositions);
        }

        // =====================================================================
        // D) FIX: MISSING RELIEF (INSERT PLACEHOLDER)
        // =====================================================================
        if (in_array('RELIEF', $missingRoles) && self::canInsertNarrativeRole('RELIEF')) {
            $sections = self::insertReliefPlaceholder($sections, $rolePositions);
        }

        // Note: PROBLEM is NOT auto-inserted (too context-dependent)
        if (in_array('PROBLEM', $missingRoles)) {
            self::$appliedFixes[] = [
                'rule' => 'NR_PROBLEM_NOT_AUTO',
                'action' => 'Missing PROBLEM role - not auto-inserted (requires manual content)',
                'stage' => 18
            ];
        }

        return $sections;
    }

    /**
     * Check if a narrative role can be safely inserted
     */
    private static function canInsertNarrativeRole(string $role): bool
    {
        // Only these roles can have safe placeholders
        $safeRoles = ['PROOF', 'RELIEF'];
        return in_array($role, $safeRoles);
    }

    /**
     * Fix CTA_BEFORE_PROMISE by swapping sections
     */
    private static function fixCtaBeforePromise(array $sections, array $rolePositions): array
    {
        $ctaPos = $rolePositions['RESOLUTION'] ?? null;
        $promisePos = $rolePositions['PROMISE'] ?? null;

        if ($ctaPos === null || $promisePos === null) {
            return $sections;
        }

        // Only swap if adjacent or 1 section apart
        $distance = abs($ctaPos - $promisePos);
        if ($distance > 2) {
            self::$appliedFixes[] = [
                'rule' => 'NR_SWAP_BLOCKED',
                'action' => "CTA_BEFORE_PROMISE: distance {$distance} > 2, swap blocked",
                'stage' => 18
            ];
            return $sections;
        }

        // Perform safe swap
        $sections = self::safeSwapSections($sections, $ctaPos, $promisePos);
        self::$narrativeSwapsCount++;

        self::$appliedFixes[] = [
            'rule' => 'NR_SWAP_CTA_PROMISE',
            'action' => "Swapped CTA (pos {$ctaPos}) with PROMISE (pos {$promisePos})",
            'stage' => 18
        ];

        // Mark sections as swapped
        if (isset($sections[$promisePos])) {
            $sections[$promisePos]['_narrative_swapped'] = true;
        }
        if (isset($sections[$ctaPos])) {
            $sections[$ctaPos]['_narrative_swapped'] = true;
        }

        return $sections;
    }

    /**
     * Fix PROOF_BEFORE_PROMISE by swapping sections
     */
    private static function fixProofBeforePromise(array $sections, array $rolePositions): array
    {
        $proofPos = $rolePositions['PROOF'] ?? null;
        $promisePos = $rolePositions['PROMISE'] ?? null;

        if ($proofPos === null || $promisePos === null) {
            return $sections;
        }

        // PROOF should come AFTER PROMISE
        if ($proofPos >= $promisePos) {
            return $sections; // Already correct
        }

        // Only swap if adjacent or 1 section apart
        $distance = abs($proofPos - $promisePos);
        if ($distance > 2) {
            self::$appliedFixes[] = [
                'rule' => 'NR_SWAP_BLOCKED',
                'action' => "PROOF_BEFORE_PROMISE: distance {$distance} > 2, swap blocked",
                'stage' => 18
            ];
            return $sections;
        }

        // Perform safe swap
        $sections = self::safeSwapSections($sections, $proofPos, $promisePos);
        self::$narrativeSwapsCount++;

        self::$appliedFixes[] = [
            'rule' => 'NR_SWAP_PROOF_PROMISE',
            'action' => "Swapped PROOF (pos {$proofPos}) with PROMISE (pos {$promisePos})",
            'stage' => 18
        ];

        // Mark sections as swapped
        if (isset($sections[$promisePos])) {
            $sections[$promisePos]['_narrative_swapped'] = true;
        }
        if (isset($sections[$proofPos])) {
            $sections[$proofPos]['_narrative_swapped'] = true;
        }

        return $sections;
    }

    /**
     * Insert PROOF placeholder section
     */
    private static function insertProofPlaceholder(array $sections, array $rolePositions): array
    {
        // Find position: after PROMISE, before DETAILS/RESOLUTION
        $promisePos = $rolePositions['PROMISE'] ?? null;
        $detailsPos = $rolePositions['DETAILS'] ?? null;
        $resolutionPos = $rolePositions['RESOLUTION'] ?? null;

        // Determine insert position
        $insertAfter = null;

        if ($promisePos !== null) {
            $insertAfter = $promisePos;
        } elseif ($detailsPos !== null && $detailsPos > 0) {
            $insertAfter = $detailsPos - 1;
        } elseif ($resolutionPos !== null && $resolutionPos > 0) {
            $insertAfter = $resolutionPos - 1;
        }

        if ($insertAfter === null) {
            self::$appliedFixes[] = [
                'rule' => 'NR_PLACEHOLDER_BLOCKED',
                'action' => 'Cannot determine position for PROOF placeholder',
                'stage' => 18
            ];
            return $sections;
        }

        // Create minimal PROOF placeholder (trust_metrics style)
        $placeholder = self::createNarrativePlaceholder('PROOF', 'trust_metrics_simple');

        // Insert after position
        $sections = self::insertSectionAfter($sections, $placeholder, $insertAfter);
        self::$narrativePlaceholdersCount++;

        self::$appliedFixes[] = [
            'rule' => 'NR_PLACEHOLDER_PROOF',
            'action' => "Inserted PROOF placeholder after position {$insertAfter}",
            'stage' => 18
        ];

        return $sections;
    }

    /**
     * Insert RELIEF placeholder section
     */
    private static function insertReliefPlaceholder(array $sections, array $rolePositions): array
    {
        // Find position: before RESOLUTION
        $resolutionPos = $rolePositions['RESOLUTION'] ?? null;

        if ($resolutionPos === null || $resolutionPos === 0) {
            self::$appliedFixes[] = [
                'rule' => 'NR_PLACEHOLDER_BLOCKED',
                'action' => 'Cannot determine position for RELIEF placeholder',
                'stage' => 18
            ];
            return $sections;
        }

        // Create minimal RELIEF placeholder (breathing_space style)
        $placeholder = self::createNarrativePlaceholder('RELIEF', 'breathing_space');

        // Insert before RESOLUTION
        $insertAfter = $resolutionPos - 1;
        $sections = self::insertSectionAfter($sections, $placeholder, $insertAfter);
        self::$narrativePlaceholdersCount++;

        self::$appliedFixes[] = [
            'rule' => 'NR_PLACEHOLDER_RELIEF',
            'action' => "Inserted RELIEF placeholder before RESOLUTION (after position {$insertAfter})",
            'stage' => 18
        ];

        return $sections;
    }

    /**
     * Create a minimal narrative placeholder section
     */
    private static function createNarrativePlaceholder(string $role, string $patternHint): array
    {
        $id = 'section_placeholder_' . strtolower($role) . '_' . time();

        // Base placeholder structure
        $placeholder = [
            'id' => $id,
            'type' => 'section',
            'attrs' => [
                'background_color' => '#F9FAFB',
                'padding' => ['top' => 60, 'right' => 0, 'bottom' => 60, 'left' => 0],
                '_placeholder' => true,
                '_autofix' => ['NARRATIVE_PLACEHOLDER'],
                '_pattern' => $patternHint,
                '_narrative_role' => $role
            ],
            '_narrative_role' => $role,
            '_placeholder' => true,
            'content' => []
        ];

        // Add minimal content based on role
        if ($role === 'PROOF') {
            $placeholder['content'][] = self::createPlaceholderRow([
                self::createPlaceholderModule('heading', [
                    'text' => 'Trusted by Industry Leaders',
                    'level' => 'h3',
                    'text_align' => 'center'
                ]),
                self::createPlaceholderModule('text', [
                    'content' => '<p style="text-align: center; color: #6B7280;">Our solutions are trusted by thousands of satisfied customers worldwide.</p>'
                ])
            ]);
        } elseif ($role === 'RELIEF') {
            $placeholder['content'][] = self::createPlaceholderRow([
                self::createPlaceholderModule('text', [
                    'content' => '<p style="text-align: center; color: #6B7280; font-size: 14px;">Take your time. We\'re here when you\'re ready.</p>'
                ])
            ]);
            $placeholder['attrs']['padding'] = ['top' => 40, 'right' => 0, 'bottom' => 40, 'left' => 0];
        }

        return $placeholder;
    }

    /**
     * Create a placeholder row with modules
     */
    private static function createPlaceholderRow(array $modules): array
    {
        return [
            'id' => 'row_placeholder_' . time() . '_' . mt_rand(1000, 9999),
            'type' => 'row',
            'attrs' => [],
            'content' => [
                [
                    'id' => 'col_placeholder_' . time() . '_' . mt_rand(1000, 9999),
                    'type' => 'column',
                    'attrs' => ['width' => '100%'],
                    'content' => $modules
                ]
            ]
        ];
    }

    /**
     * Create a placeholder module
     */
    private static function createPlaceholderModule(string $type, array $attrs): array
    {
        return [
            'id' => $type . '_placeholder_' . time() . '_' . mt_rand(1000, 9999),
            'type' => $type,
            'attrs' => array_merge($attrs, ['_placeholder' => true])
        ];
    }

    /**
     * Safely swap two sections in array
     */
    private static function safeSwapSections(array $sections, int $a, int $b): array
    {
        if (!isset($sections[$a]) || !isset($sections[$b])) {
            return $sections;
        }

        $temp = $sections[$a];
        $sections[$a] = $sections[$b];
        $sections[$b] = $temp;

        return $sections;
    }

    /**
     * Insert section after a given position
     */
    private static function insertSectionAfter(array $sections, array $newSection, int $afterIndex): array
    {
        $before = array_slice($sections, 0, $afterIndex + 1);
        $after = array_slice($sections, $afterIndex + 1);

        return array_merge($before, [$newSection], $after);
    }

    /**
     * Get Stage 18 metrics (public for generator)
     */
    public static function getNarrativeAutofixMetrics(): array
    {
        return [
            'narrative_autofix_applied' => (self::$narrativePlaceholdersCount > 0 || self::$narrativeSwapsCount > 0),
            'narrative_placeholders_count' => self::$narrativePlaceholdersCount,
            'narrative_swaps_count' => self::$narrativeSwapsCount,
            'narrative_autofix_blocked' => self::$narrativeAutofixBlocked
        ];
    }
}
