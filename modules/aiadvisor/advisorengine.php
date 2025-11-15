<?php
class AdvisorEngine {
    public static function analyzeLayout(array $layout): array {
        $issues = [];
        
        // Check block spacing consistency
        $spacingIssues = self::checkBlockSpacing($layout['blocks']);
        if (!empty($spacingIssues)) {
            $issues[] = [
                'type' => 'spacing',
                'blocks' => $spacingIssues['blocks'],
                'message' => $spacingIssues['message'],
                'severity' => 'warning'
            ];
        }

        // Check for missing headings
        if (self::hasMissingHeadings($layout['blocks'])) {
            $issues[] = [
                'type' => 'structure',
                'message' => 'Missing heading elements in content flow',
                'severity' => 'suggestion'
            ];
        }

        return [
            'layoutId' => $layout['id'] ?? 'unknown',
            'issues' => $issues,
            'generatedAt' => date('c')
        ];
    }

    private static function checkBlockSpacing(array $blocks): array {
        $spacings = [];
        $inconsistentBlocks = [];
        
        foreach ($blocks as $block) {
            if (isset($block['spacing'])) {
                $spacings[$block['id']] = $block['spacing'];
            }
        }

        // Simple check for inconsistent spacing values
        $uniqueSpacings = array_unique(array_values($spacings));
        if (count($uniqueSpacings) > 1) {
            return [
                'blocks' => array_keys($spacings),
                'message' => 'Inconsistent spacing values detected'
            ];
        }

        return [];
    }

    private static function hasMissingHeadings(array $blocks): bool {
        $hasHeading = false;
        $contentBlocks = 0;
        
        foreach ($blocks as $block) {
            if ($block['type'] === 'heading') {
                $hasHeading = true;
            } elseif (in_array($block['type'], ['text', 'image', 'cta'])) {
                $contentBlocks++;
            }
        }

        return $contentBlocks > 3 && !$hasHeading;
    }
}
