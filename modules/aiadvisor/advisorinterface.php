<?php
class AdvisorInterface {
    public static function registerWithBuilder(): void {
        BuilderEngine::registerHook('pre_render', function(array $layout, array $theme) {
            return self::analyzeLayout($layout, $theme);
        });
    }

    public static function analyzeLayout(array $layout, array $theme): array {
        require_once __DIR__.'/advisorengine.php';
        require_once __DIR__.'/AdvisorRuleset.php';

        $layoutAnalysis = AdvisorEngine::analyzeLayout($layout);
        $themeAnalysis = self::analyzeTheme($theme);

        return [
            'layout' => $layoutAnalysis,
            'theme' => $themeAnalysis,
            'combinedScore' => self::calculateScore($layoutAnalysis, $themeAnalysis)
        ];
    }

    private static function analyzeTheme(array $theme): array {
        $issues = [];
        $colors = $theme['colors'] ?? [];

        // Check color contrast
        if (isset($colors['text'], $colors['background'])) {
            $contrast = AdvisorRuleset::checkContrast($colors['text'], $colors['background']);
            if ($contrast < 4.5) {
                $issues[] = [
                    'type' => 'contrast',
                    'colors' => [$colors['text'], $colors['background']],
                    'message' => "Low contrast ratio: ".round($contrast, 2),
                    'severity' => 'warning'
                ];
            }
        }

        return [
            'themeId' => $theme['id'] ?? 'unknown',
            'issues' => $issues,
            'generatedAt' => date('c')
        ];
    }

    private static function calculateScore(array $layout, array $theme): int {
        $score = 100;
        $score -= count($layout['issues']) * 5;
        $score -= count($theme['issues']) * 10;
        return max(0, $score);
    }
}
