<?php
/**
 * JTB AI Confidence Scoring Engine
 * Deterministic confidence evaluation with stop conditions and anti-oscillation
 *
 * Measures progress (delta score, delta violations), detects stagnation/oscillation,
 * and determines decision: ACCEPT / RETRY / FAIL
 *
 * @package JessieThemeBuilder
 * @since 2.1.0
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

class JTB_AI_Confidence
{
    /**
     * Critical violations that must be resolved
     * DARK_MISUSE added in Stage 11 - pattern-aware context rules
     */
    private const CRITICAL_VIOLATIONS = [
        'MISSING_FINAL_CTA',
        'FINAL_CTA_NOT_LAST',
        'PRIMARY_COUNT_INVALID',
        'PRIMARY_MISUSE',
        'DARK_MISUSE', // Stage 11: DARK on LIGHT-only patterns
    ];

    /**
     * Evaluate confidence and determine decision
     *
     * @param array $before   Quality data before autofix (or from previous attempt)
     * @param array $after    Quality data after autofix
     * @param int   $attempt  Current attempt number (1-3)
     * @param array $history  Array of previous layout hashes for oscillation detection
     * @return array Confidence result with decision
     */
    public static function evaluate(array $before, array $after, int $attempt, array $history = []): array
    {
        $maxAttempts = 3;

        // Calculate deltas
        $scoreBefore = $before['score'] ?? 0;
        $scoreAfter = $after['score'] ?? 0;
        $scoreDelta = $scoreAfter - $scoreBefore;

        $violationsBefore = count($before['violations'] ?? []);
        $violationsAfter = count($after['violations'] ?? []);
        $violationsDelta = $violationsBefore - $violationsAfter; // Positive = improvement

        // Check critical violations
        $criticalBefore = self::extractCriticalViolations($before['violations'] ?? []);
        $criticalAfter = self::extractCriticalViolations($after['violations'] ?? []);
        $criticalRemoved = count($criticalBefore) > count($criticalAfter);
        $hasCritical = count($criticalAfter) > 0;

        // Check for oscillation (hash repeat)
        $currentHash = $after['layout_hash'] ?? '';
        $isOscillation = !empty($currentHash) && in_array($currentHash, $history);

        // ============================================================
        // Stage 11: Check for DARK_MISUSE specifically
        // This is critical because it violates Golden Page Rules
        // ============================================================
        $hasDarkMisuse = in_array('DARK_MISUSE', $criticalAfter);

        // Calculate confidence score
        $confidence = self::calculateConfidence(
            $scoreDelta,
            $violationsDelta,
            $criticalRemoved,
            $hasCritical,
            $isOscillation,
            $hasDarkMisuse // Stage 11: Additional penalty for DARK_MISUSE
        );

        // Determine decision
        $decision = self::determineDecision(
            $confidence,
            $hasCritical,
            $attempt,
            $maxAttempts,
            $isOscillation,
            $scoreDelta,
            $violationsDelta
        );

        // Determine stop reason
        $stopReason = self::determineStopReason(
            $decision,
            $confidence,
            $hasCritical,
            $isOscillation,
            $scoreDelta,
            $violationsDelta,
            $attempt,
            $maxAttempts,
            $hasDarkMisuse // Stage 11: Check for DARK_MISUSE specific stop reason
        );

        return [
            'confidence' => $confidence,
            'decision' => $decision,
            'stop_reason' => $stopReason,
            'improvement' => [
                'score_delta' => $scoreDelta,
                'violations_delta' => $violationsDelta,
                'critical_removed' => $criticalRemoved,
            ],
            'has_critical' => $hasCritical,
            'has_dark_misuse' => $hasDarkMisuse, // Stage 11: Expose for UI
            'is_oscillation' => $isOscillation,
            'critical_violations' => $criticalAfter,
        ];
    }

    /**
     * Calculate confidence score (deterministic formula)
     *
     * Base: 50
     * +10 if score_delta >= +4
     * +5  if score_delta >= +2 (but < +4)
     * +10 if removed at least 1 critical violation
     * +5  if violations_delta >= +2
     * -15 if score_delta <= 0
     * -10 if violations_delta <= 0
     * -20 if still has CRITICAL violation
     * -20 if DARK_MISUSE detected (Stage 11 - DARK on LIGHT-only pattern)
     * -25 if OSCILLATION detected (hash repeated)
     *
     * Clamped to 0..100
     */
    private static function calculateConfidence(
        int $scoreDelta,
        int $violationsDelta,
        bool $criticalRemoved,
        bool $hasCritical,
        bool $isOscillation,
        bool $hasDarkMisuse = false // Stage 11
    ): int {
        $confidence = 50;

        // Score improvements
        if ($scoreDelta >= 4) {
            $confidence += 10;
        } elseif ($scoreDelta >= 2) {
            $confidence += 5;
        }

        // Critical violation removed
        if ($criticalRemoved) {
            $confidence += 10;
        }

        // Violations count improvements
        if ($violationsDelta >= 2) {
            $confidence += 5;
        }

        // Penalties
        if ($scoreDelta <= 0) {
            $confidence -= 15;
        }

        if ($violationsDelta <= 0) {
            $confidence -= 10;
        }

        if ($hasCritical) {
            $confidence -= 20;
        }

        // Stage 11: DARK_MISUSE is a severe violation of Golden Page Rules
        // Gets its own -20 penalty (in addition to $hasCritical if applicable)
        // This ensures DARK_MISUSE is always treated seriously
        if ($hasDarkMisuse) {
            $confidence -= 20;
        }

        if ($isOscillation) {
            $confidence -= 25;
        }

        // Clamp to 0..100
        return max(0, min(100, $confidence));
    }

    /**
     * Determine decision based on confidence and conditions
     *
     * Decision logic:
     * - confidence >= 70 AND no critical → ACCEPT
     * - confidence 40..69 AND no critical → ACCEPT (acceptable quality)
     * - confidence < 40 AND attempt < maxAttempts → RETRY
     * - confidence < 40 AND attempt == maxAttempts → FAIL (if critical exists)
     * - oscillation detected → FAIL
     * - no progress (score_delta <= 0 AND violations_delta <= 0) after autofix → FAIL
     */
    private static function determineDecision(
        int $confidence,
        bool $hasCritical,
        int $attempt,
        int $maxAttempts,
        bool $isOscillation,
        int $scoreDelta,
        int $violationsDelta
    ): string {
        // Oscillation = immediate FAIL
        if ($isOscillation) {
            return 'FAIL';
        }

        // No progress after autofix = FAIL (stagnation)
        if ($scoreDelta <= 0 && $violationsDelta <= 0 && $attempt > 1) {
            return 'FAIL';
        }

        // High confidence without critical = ACCEPT
        if ($confidence >= 70 && !$hasCritical) {
            return 'ACCEPT';
        }

        // Medium confidence without critical = ACCEPT (acceptable)
        if ($confidence >= 40 && !$hasCritical) {
            return 'ACCEPT';
        }

        // Low confidence - retry if not max attempts
        if ($confidence < 40 && $attempt < $maxAttempts) {
            return 'RETRY';
        }

        // Max attempts reached with critical = FAIL
        if ($attempt >= $maxAttempts && $hasCritical) {
            return 'FAIL';
        }

        // Max attempts reached, no critical, but low confidence = forced ACCEPT
        if ($attempt >= $maxAttempts && !$hasCritical) {
            return 'ACCEPT';
        }

        // Default: RETRY if possible
        if ($attempt < $maxAttempts) {
            return 'RETRY';
        }

        return 'FAIL';
    }

    /**
     * Determine stop reason for telemetry
     */
    private static function determineStopReason(
        string $decision,
        int $confidence,
        bool $hasCritical,
        bool $isOscillation,
        int $scoreDelta,
        int $violationsDelta,
        int $attempt,
        int $maxAttempts,
        bool $hasDarkMisuse = false // Stage 11
    ): ?string {
        if ($decision === 'ACCEPT') {
            if ($confidence >= 70) {
                return 'HIGH_CONFIDENCE';
            }
            if ($confidence >= 40 && !$hasCritical) {
                return 'ACCEPTABLE_NO_CRITICAL';
            }
            if ($attempt >= $maxAttempts) {
                return 'MAX_ATTEMPTS_NO_CRITICAL';
            }
            return null;
        }

        if ($decision === 'FAIL') {
            if ($isOscillation) {
                return 'OSCILLATION_DETECTED';
            }
            // Stage 11: DARK_MISUSE-specific stop reason
            if ($hasDarkMisuse) {
                return 'DARK_MISUSE_VIOLATION';
            }
            if ($scoreDelta <= 0 && $violationsDelta <= 0) {
                return 'NO_PROGRESS_STAGNATION';
            }
            if ($hasCritical && $attempt >= $maxAttempts) {
                return 'MAX_ATTEMPTS_WITH_CRITICAL';
            }
            if ($confidence < 40) {
                return 'LOW_CONFIDENCE';
            }
            return 'UNKNOWN_FAIL';
        }

        // RETRY
        // Stage 11: DARK_MISUSE is critical, but we can retry to fix it
        if ($hasDarkMisuse) {
            return 'RETRY_DARK_MISUSE';
        }
        if ($hasCritical) {
            return 'RETRY_HAS_CRITICAL';
        }
        if ($confidence < 40) {
            return 'RETRY_LOW_CONFIDENCE';
        }

        return null;
    }

    /**
     * Extract critical violations from violations array
     */
    private static function extractCriticalViolations(array $violations): array
    {
        $critical = [];

        foreach ($violations as $violation) {
            $code = is_array($violation)
                ? ($violation['code'] ?? $violation['rule'] ?? '')
                : (string)$violation;

            // Extract code before colon if present
            $parts = explode(':', $code);
            $baseCode = trim($parts[0]);

            if (in_array($baseCode, self::CRITICAL_VIOLATIONS)) {
                $critical[] = $baseCode;
            }
        }

        return array_unique($critical);
    }

    /**
     * Generate layout hash for oscillation detection
     * Uses structure-based hashing (pattern names, visual contexts, section count)
     */
    public static function generateLayoutHash(array $layout): string
    {
        $sections = $layout['sections'] ?? $layout['content'] ?? $layout;
        if (!is_array($sections)) {
            return '';
        }

        $hashParts = [];

        foreach ($sections as $section) {
            $pattern = $section['_pattern'] ?? $section['attrs']['_pattern'] ?? 'unknown';
            $context = $section['_visual_context'] ?? $section['attrs']['visual_context'] ?? 'LIGHT';
            $rowCount = count($section['children'] ?? []);

            $hashParts[] = "{$pattern}:{$context}:{$rowCount}";
        }

        return md5(implode('|', $hashParts));
    }

    /**
     * Check if layout contains any critical violations
     */
    public static function hasCriticalViolations(array $violations): bool
    {
        return count(self::extractCriticalViolations($violations)) > 0;
    }
}
